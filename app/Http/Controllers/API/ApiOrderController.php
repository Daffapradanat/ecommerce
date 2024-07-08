<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class ApiOrderController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validatedData['product_id']);

        $order = Order::create([
            'user_id' => $validatedData['user_id'],
            'product_id' => $product->id,
            'quantity' => $validatedData['quantity'],
            'total_price' => $product->price * $validatedData['quantity'],
            'status' => 'pending',
        ]);

        $product->decrement('stock', $validatedData['quantity']);

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }
}
