<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CategoriesImport;
use App\Exports\CategoriesExport;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
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
            ->addColumn('action', function ($category) {
                $actions = '';
                if (Auth::user()->can('update', $category)) {
                    $actions .= '<a href="' . route('categories.edit', $category->id) . '" class="btn btn-warning btn-sm me-2">
                                    <i class="fas fa-edit"></i>
                                </a>';}
                if (Auth::user()->can('delete', $category)) {
                    $actions .= '<button type="button" class="btn btn-danger btn-sm me-0" data-id="' . $category->id . '">
                                    <i class="fas fa-trash"></i>
                                </button>';}
                return $actions;
            })            
                ->rawColumns(['action'])
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
        if ($category->products()->whereNull('deleted_at')->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete this category. It is still being used by one or more products.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new CategoriesImport();
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            $errors = $import->errors;

            if ($failures->isNotEmpty() || !empty($errors)) {
                $errorMessages = collect($failures)->map(function ($failure) {
                    return "Row {$failure->row()}: " . implode(', ', $failure->errors());
                })->merge($errors)->join('<br>');

                return redirect()->route('categories.index')->with('notification', [
                    'type' => 'warning',
                    'message' => 'Categories imported with some issues:<br>' . $errorMessages,
                ]);
            }

            return redirect()->route('categories.index')->with('notification', [
                'type' => 'success',
                'message' => 'Categories imported successfully.',
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = collect($failures)->map(function ($failure) {
                return "Row {$failure->row()}: " . implode(', ', $failure->errors());
            })->join('<br>');

            return redirect()->route('categories.index')->with('notification', [
                'type' => 'warning',
                'message' => 'Categories import failed:<br>' . $errorMessages,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('categories.index')->with('notification', [
                'type' => 'danger',
                'message' => 'There was an issue during import: ' . $e->getMessage(),
            ]);
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
