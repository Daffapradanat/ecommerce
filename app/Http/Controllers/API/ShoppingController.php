<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class ShoppingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');

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

    public function removeFromCart($product_id)
    {
        $deleted = Cart::where('buyer_id', auth()->id())
            ->where('product_id', $product_id)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Product removed from cart']);
        } else {
            return response()->json(['message' => 'Product not found in cart'], 404);
        }
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

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $buyer = auth()->user();
        $cartItems = Cart::where('buyer_id', $buyer->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            $totalPrice = 0;
            $items = [];

            $order = Order::create([
                'buyer_id' => $buyer->id,
                'status' => 'pending',
                'total_price' => 0,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'email' => $buyer->email,
                'phone' => $request->phone,
                'city' => $request->city,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
            ]);

            foreach ($cartItems as $item) {
                $itemPrice = $item->quantity * $item->product->price;
                $totalPrice += $itemPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);

                $items[] = [
                    'id' => $item->product_id,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'name' => $item->product->name,
                ];
            }

            $order->update(['total_price' => $totalPrice]);

            $params = [
                'transaction_details' => [
                    'order_id' => $order->id,
                    'gross_amount' => $totalPrice,
                ],
                'item_details' => $items,
                'customer_details' => [
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
                'order' => $order,
                'payment_token' => $snapToken
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout failed', 'error' => $e->getMessage()], 500);
        }
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
}
