<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
<<<<<<< HEAD
=======
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CategoriesImport;
use App\Exports\CategoriesExport;
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
<<<<<<< HEAD
            $categories = Category::withCount('products');

            return DataTables::of($categories)
=======
            $search = $request->input('search.value');

            $query = Category::withCount(['products' => function ($query) {
                $query->whereNull('deleted_at');
            }]);

            if ($search) {
                $query->where(function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            return DataTables::of($query)
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
                ->addColumn('action', function ($category) {
                    $editBtn = '<a href="' . route('categories.edit', $category->id) . '" class="btn btn-warning btn-sm me-2"><i class="fas fa-edit"></i></a>';
                    $deleteBtn = '<button type="button" class="btn btn-danger btn-sm me-0" data-id="' . $category->id . '"><i class="fas fa-trash"></i></button>';
                    return $editBtn . $deleteBtn;
                })
                ->rawColumns(['action'])
<<<<<<< HEAD
                ->filterColumn('name', function($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })
                ->filterColumn('slug', function($query, $keyword) {
                    $query->where('slug', 'like', "%{$keyword}%");
                })
=======
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
                ->make(true);
        }

        return view('categories.index');
    }

    public function create()
    {
        return view('categories.create');
    }

    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
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
<<<<<<< HEAD
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete this category. It is still being used by one or more products.');
        }

        $category->delete();

=======
        if ($category->products()->whereNull('deleted_at')->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete this category. It is still being used by one or more products.');
        }
    
        $category->delete();
    
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new CategoriesImport, $request->file('file'));
            return redirect()->route('categories.index')->with('success', 'Categories imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('categories.index')->with('error', 'Error importing categories: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new CategoriesExport, 'categories.xlsx');
    }

    public function downloadTemplate()
    {
        $templatePath = base_path('app/template/categories_template.xlsx');
        return response()->download($templatePath);
    }

}
