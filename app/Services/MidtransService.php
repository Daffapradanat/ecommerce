<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function getSnapToken(Order $order)
    {
        try {
            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_id,
                    'gross_amount' => (int) $order->total_price,
                ],
                'customer_details' => [
                    'first_name' => $order->name,
                    'email' => $order->email,
                    'phone' => $order->phone,
                ],
            ];

            return Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Midtrans getSnapToken error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleCallback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);

        if ($hashed == $request->signature_key && $request->transaction_status == 'capture') {
            $order = Order::where('order_id', $request->order_id)->first();
            if ($order) {
                $order->payment_status = 'paid';
                $order->save();
            }
        }
    }

    private function buildMidtransParams(Order $order)
    {
        $items = $order->orderItems->map(function ($item) {
            return [
                'id' => $item->product->id,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'name' => $item->product->name
            ];
        })->toArray();

        return [
            'transaction_details' => [
                'order_id' => $order->order_id,
                'gross_amount' => $order->total_price,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->buyer->name,
                'email' => $order->buyer->email,
                'phone' => $order->buyer->phone,
            ],
        ];
    }
}
