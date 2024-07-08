<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::updateOrCreate(
            [
                'user_id' => $validatedData['user_id'],
                'product_id' => $validatedData['product_id'],
            ],
            [
                'quantity' => \DB::raw('quantity + ' . $validatedData['quantity']),
            ]
        );

        return response()->json(['message' => 'Product added to cart', 'cart' => $cart], 201);
    }

    public function getCart($userId)
    {
        $cartItems = Cart::where('user_id', $userId)
            ->with('product')
            ->get();

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return response()->json([
            'cart_items' => $cartItems,
            'total' => $total
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ]);

        Cart::where('user_id', $validatedData['user_id'])
            ->where('product_id', $validatedData['product_id'])
            ->delete();

        return response()->json(['message' => 'Product removed from cart']);
    }
}
