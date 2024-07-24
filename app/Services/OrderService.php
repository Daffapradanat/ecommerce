<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderService
{
    public function getFilteredOrders(Request $request)
    {
        $query = Order::with(['orderItems.product', 'buyer']);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function updateOrderStatus($id, $status)
    {
        $order = Order::findOrFail($id);
        $order->status = $status;
        $order->save();
        return $order;
    }

    public function deleteOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->orderItems()->delete();
        $order->delete();
    }

    public function cancelPayment($id)
    {
        $order = Order::findOrFail($id);

        if (!in_array($order->payment_status, ['pending', 'awaiting_payment'])) {
            return [
                'status' => 'error',
                'message' => 'This payment cannot be cancelled.'
            ];
        }

        try {

            $order->update([
                'payment_status' => 'cancelled',
                'payment_token' => null
            ]);

            return [
                'status' => 'success',
                'message' => 'Payment has been cancelled successfully.'
            ];
        } catch (\Exception $e) {
            \Log::error('Payment cancellation error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to cancel payment. Please try again later.'
            ];
        }
    }
}
