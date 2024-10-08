<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Buyer;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Http\Requests\StoreProductRequest;
use App\Notifications\NewProductNotification;
use App\Notifications\ImportedProductsNotification;
use App\Mail\NotificationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with(['category', 'image']);

            $columnIndex = $request->input('order.0.column', 0);
            $direction = $request->input('order.0.dir', 'asc');

            $columns = [
                'image',
                'name',
                'price',
                'stock',
                'category',
                'action',
            ];

            $sortColumn = $columns[$columnIndex] ?? null;

            if ($sortColumn === 'price' || $sortColumn === 'stock') {
                $query = $query->orderBy($sortColumn, $direction);
            }

            return DataTables::of($query)
                ->addColumn('image', function ($product) {
                    if ($product->image->isNotEmpty()) {
                        return '<img src="'.asset('storage/'.$product->image->first()->path).'" alt="'.$product->name.'" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">';
                    } else {
                        return '<div class="bg-secondary text-white d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;"><i class="fas fa-image"></i></div>';
                    }
                })
                ->addColumn('price', function ($product) {
                    return 'Rp '.number_format($product->price, 0, ',', '.');
                })
                ->addColumn('stock', function ($product) {
                    if ($product->stock > 50) {
                        return '<span class="badge bg-success">'.$product->stock.'</span>';
                    } elseif ($product->stock > 10) {
                        return '<span class="badge bg-warning">'.$product->stock.'</span>';
                    } elseif ($product->stock > 0) {
                        return '<span class="badge bg-danger">'.$product->stock.'</span>';
                    } else {
                        return '<span class="badge bg-secondary">Out of Stock</span>';
                    }
                })
                ->addColumn('category', function ($product) {
                    return $product->category->name;
                })
                ->addColumn('action', function ($product) {
                    $actions = '';
                    $user = Auth::user();
                    $canUpdate = $user->can('update', $product);
                    $canDelete = $user->can('delete', $product);

                    \Log::info("User {$user->id} permissions for product {$product->id}: update: " . ($canUpdate ? 'yes' : 'no') . ", delete: " . ($canDelete ? 'yes' : 'no'));

                    $actions .= '<a href="'.route('products.show', $product->id).'" class="btn btn-info btn-sm me-2">
                                    <i class="fas fa-eye"></i>
                                </a>';

                        if (Auth::user()->can('products.edit')) {
                            $actions .= '<a href="'.route('products.edit', $product->id).'" class="btn btn-warning btn-sm me-2">
                                            <i class="fas fa-edit"></i>
                                        </a>';
                        }
                        if (Auth::user()->can('products.delete')) {
                            $actions .= '<button type="button" class="btn btn-danger btn-sm me-0" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="'.$product->id.'">
                                            <i class="fas fa-trash"></i>
                                        </button>';
                        }
                    return $actions;
                })
                ->filterColumn('category', function ($query, $keyword) {
                    $query->whereHas('category', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['image', 'stock', 'action'])
                ->make(true);
        }

        $categories = Category::all();

        return view('products.index', compact('categories'));
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

            $buyers = Buyer::all();
            foreach ($buyers as $buyer) {
                $buyer->notify(new NewProductNotification($product));
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
            'remove_images.*' => 'exists:images,id',
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
        foreach ($product->image as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
        $product->delete();

        session()->flash('notification', [
            'type' => 'success',
            'message' => 'Product deleted successfully',
        ]);

        return redirect()->route('products.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new ProductsImport();
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            $errors = $import->errors;

            if ($failures->isNotEmpty() || !empty($errors)) {
                $errorMessages = collect($failures)->map(function ($failure) {
                    return "Row {$failure->row()}: ".$failure->errors()[0];
                })->merge($errors)->join('');

                return redirect()->route('products.index')->with('notification', [
                    'type' => 'warning',
                    'message' => 'Products imported with some issues:'.$errorMessages,
                ]);
            }

            $importCount = $import->getRowCount();

            $buyers = Buyer::all();
            foreach ($buyers as $buyer) {
                $buyer->notify(new ImportedProductsNotification($importCount));
            }

            $users = User::all();
            foreach ($users as $user) {
                $user->notify(new ImportedProductsNotification($importCount));
            }

            return redirect()->route('products.index')->with('notification', [
                'type' => 'success',
                'message' => 'Products imported successfully. ' . $importCount . ' new products added.',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('products.index')->with('notification', [
                'type' => 'danger',
                'message' => 'There was an issue during import: '.$e->getMessage(),
            ]);
        }
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function downloadTemplate()
    {
        $filePath = base_path('app/template/products_template.xlsx');

        if (! file_exists($filePath)) {
            abort(404, 'Template file not found.');
        }

        return response()->download($filePath, 'products_template.xlsx');
    }
}
