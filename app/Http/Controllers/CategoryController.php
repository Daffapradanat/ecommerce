<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = Category::withCount('products');

            return DataTables::of($categories)
                ->addColumn('action', function ($category) {
                    $editBtn = '<a href="' . route('categories.edit', $category->id) . '" class="btn btn-warning btn-sm me-2"><i class="fas fa-edit"></i></a>';
                    $deleteBtn = '<button type="button" class="btn btn-danger btn-sm me-0" data-id="' . $category->id . '"><i class="fas fa-trash"></i></button>';
                    return $editBtn . $deleteBtn;
                })
                ->rawColumns(['action'])
                ->filterColumn('name', function($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })
                ->filterColumn('slug', function($query, $keyword) {
                    $query->where('slug', 'like', "%{$keyword}%");
                })
                ->make(true);
        }

        return view('categories.index');
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);

        if (Category::where('slug', $slug)->exists()) {
            return redirect()->route('categories.create')
                ->with('error', 'A category with this name already exists.')
                ->withInput();
        }

        Category::create([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);

        if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            return redirect()->route('categories.edit', $category->id)
                ->with('error', 'A category with this name already exists.')
                ->withInput();
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete this category. It is still being used by one or more products.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully');
    }
}
