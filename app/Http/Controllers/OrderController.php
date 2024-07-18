<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['orderItems.product', 'buyer'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['orderItems.product', 'buyer'])->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending orders can be deleted'], 403);
        }

        try {
            $order->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete order'], 500);
        }
    }
}
