<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('image')->get()->map(function ($product) {
            $product->image_urls = $product->image->map(function ($image) {
                return url('storage/' . $image->path);
            });
            unset($product->image);

            return $product;
        });

        return response()->json(['products' => $products], 200);
    }
}
