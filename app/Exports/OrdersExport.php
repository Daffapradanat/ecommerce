<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Order::query()->with(['buyer', 'orderItems.product']);
    }

    public function headings(): array
    {
        return [
            'ID', 'Order ID', 'Items', 'Total Price',
            'Buyer ID', 'Email', 'Phone', 'Address', 'City', 'Postal Code',
            'Payment Token', 'Payment Method', 'Payment Status', 'Snap Token',
            'Created At', 'Updated At'
        ];
    }

    public function map($order): array
    {
        $items = $order->orderItems->map(function ($item) {
            return "{$item->product->name} ({$item->quantity})";
        })->implode(', ');

        return [
            $order->id,
            $order->order_id,
            $items,
            $order->total_price,
            $order->buyer_id,
            $order->email,
            $order->phone,
            $order->address,
            $order->city,
            $order->postal_code,
            $order->payment_token,
            $order->payment_method,
            $order->payment_status,
            $order->snap_token,
            $order->created_at,
            $order->updated_at
        ];
    }
}
