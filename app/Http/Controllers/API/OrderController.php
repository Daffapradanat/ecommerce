<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show($id): View
    {
        $order = Order::with('orderItems.product')->findOrFail($id);

        return view('orders.show', compact('order'));
    }
}
