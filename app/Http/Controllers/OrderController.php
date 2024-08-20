<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Midtrans\Config;
use Midtrans\Snap;
use App\Services\OrderService;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\OrderCancelledNotification;
use App\Services\MidtransService;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;
use PDF;

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
            if ($request->ajax()) {
                $query = Order::with('buyer');

                return DataTables::of($query)
                    ->addColumn('action', function ($order) {
                        $viewBtn = '<a href="' . route('orders.show', $order->id) . '" class="btn btn-info btn-sm me-2"><i class="fas fa-eye"></i></a>';
                        $cancelBtn = '';
                        if ($order->payment_status === 'awaiting_payment' || $order->payment_status === 'pending') {
                            $cancelBtn = '<button type="button" class="btn btn-danger btn-sm delete-order" data-id="' . $order->id . '"><i class="fas fa-trash"></i></button>';
                        }
                        return $viewBtn . $cancelBtn;
                    })
                    ->editColumn('total_price', function ($order) {
                        return 'Rp ' . number_format($order->total_price, 0, ',', '.');
                    })
                    ->editColumn('payment_status', function ($order) {
                        $statusClass = [
                            'pending' => 'warning',
                            'awaiting_payment' => 'info',
                            'paid' => 'success',
                            'failed' => 'danger',
                            'cancelled' => 'danger'
                        ][$order->payment_status] ?? 'secondary';

                        return '<span class="badge bg-' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $order->payment_status)) . '</span>';
                    })
                    ->editColumn('created_at', function ($order) {
                        return $order->created_at->format('d M Y H:i');
                    })
                    ->rawColumns(['action', 'payment_status'])
                    ->make(true);
            }

            return view('orders.index');
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
        $order = Order::findOrFail($id);
        $oldStatus = $order->payment_status;

        if (!in_array($order->payment_status, ['awaiting_payment', 'pending'])) {
            return response()->json(['message' => 'This order cannot be cancelled.'], 400);
        }

        $order->payment_status = 'cancelled';
        $order->save();

        if ($oldStatus !== 'cancelled') {

            $order->buyer->notify(new OrderCancelledNotification($order));

            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new OrderCancelledNotification($order));
        }

        return response()->json(['message' => 'Order cancelled successfully.']);
    }

    public function pay($id)
    {
        $order = Order::findOrFail($id);
        $oldStatus = $order->payment_status;

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

            if ($oldStatus !== 'awaiting_payment') {
                $order->buyer->notify(new OrderStatusChangedNotification($order, $oldStatus));

                $admins = User::where('role', 'admin')->get();
                \Illuminate\Support\Facades\Notification::send($admins, new OrderStatusChangedNotification($order, $oldStatus));
            }

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
        $oldStatus = $order->payment_status;

        if ($oldStatus !== 'paid') {
            $order->payment_status = 'paid';
            $order->save();

            $order->buyer->notify(new OrderStatusChangedNotification($order, $oldStatus));


            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new OrderStatusChangedNotification($order, $oldStatus));
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully.'
        ]);
    }

    public function cancelPayment($id)
    {
        $result = $this->orderService->cancelPayment($id);
        $order = Order::findOrFail($id);
        $oldStatus = $order->payment_status;

        if ($result['status'] === 'success') {
            if ($oldStatus !== 'cancelled') {
                $order->buyer->notify(new OrderCancelledNotification($order));

                $admins = User::where('role', 'admin')->get();
                Notification::send($admins, new OrderCancelledNotification($order));
            }

            if (request()->ajax()) {
                return response()->json(['message' => $result['message']], 200);
            }
            return redirect()->route('orders.show', $id)->with('success', $result['message']);
        } else {
            if (request()->ajax()) {
                return response()->json(['message' => $result['message']], 400);
            }
            return redirect()->route('orders.show', $id)->with('error', $result['message']);
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

        $oldStatus = $order->payment_status;

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

        $order->buyer->notify(new OrderStatusChangedNotification($order, $oldStatus));
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new OrderStatusChangedNotification($order, $oldStatus));

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
        $oldStatus = $order->payment_status;

        switch ($request->transaction_status) {
            case 'capture':
            case 'settlement':
                $order->payment_status = 'paid';
                break;
            case 'pending':
                $order->payment_status = 'pending';
                break;
            case 'deny':
            case 'expire':
            case 'cancel':
                $order->payment_status = 'failed';
                break;
        }

        $order->save();

        if ($order->payment_status !== $oldStatus) {
            $order->buyer->notify(new OrderStatusChangedNotification($order, $oldStatus));
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new OrderStatusChangedNotification($order, $oldStatus));
        }

        return response('OK', 200);
    }

    public function export()
    {
        return Excel::download(new OrdersExport, 'orders.xlsx');
    }

    public function downloadInvoice($id)
    {
        $order = Order::with(['buyer', 'orderItems.product'])->findOrFail($id);

        $this->authorize('view', $order);

        $pdf = PDF::loadView('emails.invoice', ['order' => $order]);

        return $pdf->download('invoice-' . $order->order_id . '.pdf');
    }

}
