<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all()->map(function ($product) {
            $product->image_urls = collect($product->images)->map(function ($image) {
                return url('storage/' . $image);
            });
            unset($product->images);

            return $product;
        });

        return response()->json(['products' => $products], 200);
    }
}
