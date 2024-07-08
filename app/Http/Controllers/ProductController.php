<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['image', 'category'])->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $images = Image::all();

        return view('products.create', compact('categories', 'images'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'image_id' => 'required|exists:images,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($validatedData);

        return redirect()->route('products.show', $product->id)->with('success', 'Product created successfully');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $images = Image::all();

        return view('products.edit', compact('product', 'categories', 'images'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'image_id' => 'required|exists:images,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product->update($validatedData);

        return redirect()->route('products.show', $product->id)->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        session()->flash('notification', [
            'type' => 'success',
            'message' => 'Product deleted successfully',
        ]);

        return redirect()->route('products.index');
    }
}
