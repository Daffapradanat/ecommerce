<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;


class ShoppingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('throttle:60,1')->only(['cancelOrder']);

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::updateOrCreate(
            [
                'buyer_id' => auth()->id(),
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return response()->json(['message' => 'Product added to cart', 'cart' => $cart], 201);
    }

    public function showCart()
    {
        $cartItems = Cart::where('buyer_id', auth()->id())
            ->with('product.image')
            ->get();

        $cartItems = $cartItems->map(function ($item) {
            $product = $item->product;

            if ($product->image) {
                $product->image_urls = $product->image->map(function ($image) {
                    return url('storage/'.$image->path);
                });
            } else {
                $product->image_urls = collect();
            }

            unset($product->image);

            return $item;
        });

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return response()->json([
            'cart_items' => $cartItems,
            'total' => $total,
        ]);
    }

    public function editCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('buyer_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if (! $cart) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated', 'cart' => $cart]);
    }

    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $deleted = Cart::where('buyer_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Product removed from cart']);
        } else {
            return response()->json(['message' => 'Product not found in cart'], 404);
        }
    }

    private function generateUniqueOrderId()
    {
        do {
            $orderId = 'ORDER-'.strtoupper(Str::random(10));
        } while (Order::where('order_id', $orderId)->exists());

        return $orderId;
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:bank_transfer,credit_card,gopay,shopeepay',
            'phone' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'required|string|max:10',
        ]);

        $buyer = auth()->user();
        $cartItems = Cart::where('buyer_id', $buyer->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            $totalPrice = $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            $order = Order::create([
                'order_id' => $this->generateUniqueOrderId(),
                'buyer_id' => $buyer->id,
                'status' => 'pending',
                'total_price' => $totalPrice,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'email' => $buyer->email,
                'phone' => $request->phone,
                'city' => $request->city,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
            ]);

            $orderItems = $cartItems->map(function ($item) use ($order) {
                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);

                return $orderItem;
            });

            $order->orderItems()->saveMany($orderItems);

            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_id,
                    'gross_amount' => $totalPrice,
                ],
                'item_details' => $orderItems->map(function ($item) {
                    return [
                        'id' => $item->product_id,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'name' => $item->product->name,
                    ];
                }),
                'customer_details' => [
                    'first_name' => $buyer->name,
                    'email' => $buyer->email,
                    'phone' => $request->phone,
                    'billing_address' => [
                        'city' => $request->city,
                        'postal_code' => $request->postal_code,
                        'address' => $request->address,
                    ],
                    'shipping_address' => [
                        'city' => $request->city,
                        'postal_code' => $request->postal_code,
                        'address' => $request->address,
                    ],
                ],
                'enabled_payments' => [$request->payment_method],
            ];

            $snapToken = Snap::getSnapToken($params);
            $order->update(['payment_token' => $snapToken]);

            Cart::where('buyer_id', $buyer->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('orderItems.product'),
                'payment_token' => $snapToken,
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$snapToken,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Checkout failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function handlePaymentNotification(Request $request)
    {
        $notificationBody = json_decode($request->getContent(), true);
        $transactionStatus = $notificationBody['transaction_status'];
        $fraudStatus = $notificationBody['fraud_status'];
        $orderId = $notificationBody['order_id'];

        $order = Order::where('order_id', $orderId)->firstOrFail();

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $order->setStatusPending();
            } elseif ($fraudStatus == 'accept') {
                $order->setStatusSuccess();
            }
        } elseif ($transactionStatus == 'settlement') {
            $order->setStatusSuccess();
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $order->setStatusFailed();
        } elseif ($transactionStatus == 'pending') {
            $order->setStatusPending();
        }

        return response('OK', 200);
    }

    public function listOrders()
    {
        $orders = Order::where('buyer_id', auth()->id())
            ->with('orderItems.product.image')
            ->orderBy('created_at', 'desc')
            ->get();

        $orders = $orders->map(function ($order) {
            $order->orderItems = $order->orderItems->map(function ($item) {
                $product = $item->product;

                if ($product->image) {
                    $product->image_urls = $product->image->map(function ($image) {
                        return url('storage/'.$image->path);
                    });
                } else {
                    $product->image_urls = collect();
                }

                unset($product->image);

                return $item;
            });

            return $order;
        });

        return response()->json(['orders' => $orders]);
    }

    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,order_id'
        ]);

        $order = Order::where('order_id', $request->order_id)
            ->where('buyer_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found or you are not authorized to cancel this order'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled'], 400);
        }

        $order->setStatusFailed();

        return response()->json(['message' => 'Order cancelled successfully', 'order' => $order]);
    }

    public function getPaymentLink($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->payment_status !== 'pending') {
            return response()->json(['error' => 'This order is not pending payment'], 400);
        }

        // Ambil payment token dari order
        $paymentToken = $order->payment_token;

        if (! $paymentToken) {
            return response()->json(['error' => 'Payment token not found'], 404);
        }

        // Gunakan Midtrans SDK untuk mendapatkan redirect URL
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');

        try {
            $paymentUrl = Snap::getTransactionRedirectUrl($paymentToken);

            return response()->json(['payment_url' => $paymentUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get payment URL: '.$e->getMessage()], 500);
        }
    }
}
