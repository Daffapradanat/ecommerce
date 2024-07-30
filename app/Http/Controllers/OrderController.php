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
            $status = $request->input('status', 'All');
            $search = $request->input('search');
            $date = $request->input('date');

            $query = Order::with('buyer');

            if ($status !== 'All') {
                if ($status === 'Failed & Cancelled') {
                    $query->whereIn('payment_status', ['failed', 'cancelled']);
                } else {
                    $query->where('payment_status', strtolower(str_replace(' ', '_', $status)));
                }
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('order_id', 'like', "%{$search}%")
                    ->orWhereHas('buyer', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                });
            }

            if ($date) {
                $query->whereDate('created_at', $date);
            }

            $orders = $query->latest()->paginate(10);
            $totalFilteredOrders = $orders->total();

            return view('orders.index', compact('orders', 'totalFilteredOrders', 'status'));
        }

    public function show($id)
    {
        $order = Order::with('orderItems.product')->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if ($order->payment_status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be deleted.'], 400);
        }
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully.']);
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

            $order->update([
                'payment_token' => $snapToken,
                'payment_status' => 'awaiting_payment',
            ]);

            $expirationTime = now()->addMinutes(5);
            session(['payment_expires_at_' . $order->id => $expirationTime->timestamp]);

            return view('orders.pay', compact('order', 'snapToken'));
        } catch (\Exception $e) {
            \Log::error('Midtrans getSnapToken error: ' . $e->getMessage());
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Failed to generate payment token. Please try again later.');
        }
    }

    public function checkPayment($id)
    {
        $order = Order::findOrFail($id);
        return response()->json(['status' => $order->payment_status]);
    }


    public function completePayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $order->payment_status = 'paid';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully.'
        ]);
    }

    public function cancelPayment($id)
    {
        $result = $this->orderService->cancelPayment($id);

        if ($result['status'] === 'success') {
            if (request()->ajax()) {
                return response()->json(['message' => $result['message']], 200);
            }
            return redirect()->route('orders.show', $id)
                ->with('success', $result['message']);
        } else {
            if (request()->ajax()) {
                return response()->json(['message' => $result['message']], 400);
            }
            return redirect()->route('orders.show', $id)
                ->with('error', $result['message']);
        }
    }


    public function handlePaymentCallback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);
        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'capture') {
                $order = Order::where('order_id', $request->order_id)->firstOrFail();
                $order->payment_status = 'paid';
                $order->save();
            }
        }

        return response('OK', 200);
    }

    public function midtransCallback(Request $request)
    {
        Log::info('Midtrans callback received', $request->all());

        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);

        if ($hashed != $request->signature_key) {
            Log::warning('Invalid signature for order: ' . $request->order_id);
            return response('Invalid signature', 403);
        }

        $order = Order::where('order_id', $request->order_id)->first();

        if (!$order) {
            Log::error('Order not found: ' . $request->order_id);
            return response('Order not found', 404);
        }

        Log::info('Processing order: ' . $order->id);

        switch ($request->transaction_status) {
            case 'capture':
            case 'settlement':
                $order->payment_status = 'paid';
                Log::info('Payment for order ' . $order->id . ' marked as paid');
                break;
            case 'pending':
                $order->payment_status = 'pending';
                Log::info('Payment for order ' . $order->id . ' marked as pending');
                break;
            case 'deny':
            case 'expire':
            case 'cancel':
                $order->payment_status = 'failed';
                Log::info('Payment for order ' . $order->id . ' marked as failed');
                break;
        }

        $order->save();

        return response('OK', 200);
    }

}
