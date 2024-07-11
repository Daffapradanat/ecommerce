<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'image'])->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = Product::create($validatedData);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('product_images', 'public');
                $product->image()->create(['path' => $path]);
            }
        }

        return redirect()->route('products.index')->with('notification', [
            'type' => 'success',
            'message' => 'Product created successfully',
        ]);
    }

    public function show($id)
    {
        $product = Product::with('image')->findOrFail($id);
        return view('products.show', compact('product'));
    }


    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_images' => 'array',
            'remove_images.*' => 'exists:images,id'
        ]);

        $product->update($validatedData);

        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imageId) {
                $image = $product->image()->find($imageId);
                if ($image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('product_images', 'public');
                $product->image()->create(['path' => $path]);
            }
        }

        return redirect()->route('products.index')->with('notification', [
            'type' => 'success',
            'message' => 'Product updated successfully',
        ]);
    }

    public function destroy(Product $product)
    {
        foreach ($product->image as $images) {
            Storage::disk('public')->delete($images->path);
            $images->delete();
        }

        $product->delete();

        session()->flash('notification', [
            'type' => 'success',
            'message' => 'Product deleted successfully',
        ]);

        return redirect()->route('products.index');
    }

}
