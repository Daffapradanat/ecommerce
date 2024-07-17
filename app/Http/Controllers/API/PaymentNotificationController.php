<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Midtrans\Notification;

class PaymentNotificationController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $notification = new Notification();

            $order = Order::findOrFail($notification->order_id);
            $transaction = $notification->transaction_status;
            $type = $notification->payment_type;
            $fraud = $notification->fraud_status;

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if($fraud == 'challenge') {
                        $order->setStatusPending();
                    } else {
                        $order->setStatusSuccess();
                    }
                }
            } elseif ($transaction == 'settlement') {
                $order->setStatusSuccess();
            } elseif($transaction == 'pending') {
                $order->setStatusPending();
            } elseif ($transaction == 'deny') {
                $order->setStatusFailed();
            } elseif ($transaction == 'expire') {
                $order->setStatusExpired();
            } elseif ($transaction == 'cancel') {
                $order->setStatusFailed();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment notification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
