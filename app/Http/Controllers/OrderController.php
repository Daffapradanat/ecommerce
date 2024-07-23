<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;
use App\Services\OrderService;
use App\Services\MidtransService;

class OrderController extends Controller
{
        protected $orderService;
        protected $midtransService;

        public function __construct(OrderService $orderService, MidtransService $midtransService)
        {
            $this->orderService = $orderService;
            $this->midtransService = $midtransService;
            Config::$serverKey = config('midtrans.server_key');
            Config::$clientKey = config('midtrans.client_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = config('midtrans.is_sanitized');
            Config::$is3ds = config('midtrans.is_3ds');
        }

    public function index(Request $request)
    {
        $orders = $this->orderService->getFilteredOrders($request);
        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with('orderItems.product')->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    public function destroy($id)
    {
        $this->orderService->deleteOrder($id);
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }

    public function cancel($id)
    {
        $result = $this->orderService->cancelOrder($id);
        return redirect()->route('orders.index')->with($result['status'], $result['message']);
    }

    public function pay($id)
    {
        $order = Order::findOrFail($id);

        if (!in_array($order->payment_status, ['pending', 'awaiting_payment'])) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'This order cannot be paid.');
        }

        try {
            $snapToken = $this->midtransService->getSnapToken($order);
        } catch (\Exception $e) {
            Log::error('Midtrans getSnapToken error: ' . $e->getMessage());
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Failed to generate payment token. Please try again later.');
        }

        $order->update([
            'payment_token' => $snapToken,
            'payment_status' => 'awaiting_payment',
            'status' => 'pending'
        ]);

        $expirationTime = now()->addMinutes(5);
        session(['payment_expires_at_' . $order->id => $expirationTime->timestamp]);

        return view('orders.pay', compact('order', 'snapToken'));
    }

    public function checkPaymentStatus($id)
    {
        $order = Order::findOrFail($id);
        $expiresAt = session('payment_expires_at_' . $order->id);

        if ($order->payment_status === 'paid') {
            return response()->json(['status' => 'paid', 'redirect' => route('orders.index')]);
        } elseif ($order->payment_status === 'failed' || ($expiresAt && $expiresAt < now()->timestamp)) {
            if ($order->payment_status !== 'failed') {
                $order->payment_status = 'failed';
                $order->status = 'cancelled';
                $order->save();
            }
            return response()->json(['status' => 'failed']);
        } else {
            return response()->json(['status' => 'unpaid']);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $order = $this->orderService->updateOrderStatus($id, $request->status);
        if ($request->status === 'payment_abandoned') {
            $order->payment_status = 'pending';
            $order->save();
        }
        return response()->json(['success' => true, 'message' => 'Order status updated successfully.']);
    }

    public function cancelPayment($id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_status === 'awaiting_payment') {
            $order->payment_status = 'pending';
            $order->status = 'pending';
            $order->payment_token = null;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment process was cancelled successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to cancel payment. The order is not in the correct state.'
        ]);
    }

    public function handlePaymentCallback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);
        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'capture') {
                $order = Order::where('order_id', $request->order_id)->firstOrFail();
                $order->payment_status = 'paid';
                $order->status = 'completed';
                $order->save();
            }
        }

        return response('OK', 200);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $newStatus = $request->status;

        if (!in_array($newStatus, ['paid', 'failed', 'pending', 'awaiting_payment'])) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 400);
        }

        $order->payment_status = $newStatus;
        if ($newStatus === 'paid') {
            $order->status = 'completed';
        } elseif ($newStatus === 'failed') {
            $order->status = 'cancelled';
        } elseif ($newStatus === 'awaiting_payment') {
            $order->status = 'pending';
        }
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'new_status' => $newStatus
        ]);
    }

        public function completePayment($id)
    {
        $order = Order::findOrFail($id);
        $order->payment_status = 'paid';
        $order->status = 'completed';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully.'
        ]);
    }

    public function midtransCallback(Request $request)
    {
        try {
            $serverKey = config('midtrans.server_key');
            $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);

            if ($hashed != $request->signature_key) {
                Log::warning('Midtrans Callback: Invalid signature key for order ' . $request->order_id);
                return response('Invalid signature', 403);
            }

            $order = Order::where('order_id', $request->order_id)->firstOrFail();

            switch ($request->transaction_status) {
                case 'capture':
                case 'settlement':
                    $order->payment_status = 'paid';
                    $order->status = 'completed';
                    $logMessage = 'Payment processed successfully';
                    break;
                case 'pending':
                    $order->payment_status = 'pending';
                    $logMessage = 'Payment pending';
                    break;
                default:
                    $order->payment_status = 'failed';
                    $order->status = 'cancelled';
                    $logMessage = 'Payment failed';
            }

            $order->save();
            Log::info("Midtrans Callback: $logMessage for order " . $request->order_id);

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    // public function getSnapToken(Order $order)
    // {
    //     try {
    //         $params = [
    //             'transaction_details' => [
    //                 'order_id' => $order->order_id,
    //                 'gross_amount' => $order->total_price,
    //             ],
    //             'customer_details' => [
    //                 'first_name' => $order->name,
    //                 'email' => $order->email,
    //                 'phone' => $order->phone,
    //             ],
    //         ];

    //         $snapToken = \Midtrans\Snap::getSnapToken($params);
    //         return $snapToken;
    //     } catch (\Exception $e) {
    //         Log::error('Midtrans getSnapToken error: ' . $e->getMessage());
    //         return null;
    //     }
    // }
}
