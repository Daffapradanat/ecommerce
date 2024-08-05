<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BuyerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Buyer::query();

            return DataTables::of($query)
                ->addColumn('image', function ($buyer) {
                    if ($buyer->image && Storage::disk('public')->exists('buyers/' . $buyer->image)) {
                        return '<img src="' . asset('storage/buyers/' . $buyer->image) . '" alt="' . $buyer->name . '" class="rounded-circle" width="50" height="50" style="object-fit: cover;">';
                    } else {
                        return '<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px;">' . strtoupper(substr($buyer->name, 0, 1)) . '</div>';
                    }
                })
                ->addColumn('action', function ($buyer) {
                    return '<div class="d-flex justify-content-start align-items-center">
                                <a href="' . route('buyer.show', $buyer->id) . '" class="btn btn-info btn-sm me-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm me-0" data-bs-toggle="modal" data-bs-target="#deleteModal' . $buyer->id . '">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('buyers.index');
    }

    public function create()
    {
        return view('buyers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:buyers',
            'password' => 'required|string|min:8|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $buyer = new buyer();
        $buyer->name = $request->name;
        $buyer->email = $request->email;
        $buyer->password = Hash::make($request->password);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('buyers', 'public');
            $buyer->image = basename($imagePath);
        }

        $buyer->save();

        return redirect()->route('buyer.index')->with('success', 'Buyer created successfully.');
    }

    public function show(buyer $buyer)
    {
        return view('buyers.show', compact('buyer'));
    }

    public function edit(Buyer $buyer)
    {
        return view('buyers.edit', compact('buyer'));
    }

    public function update(Request $request, buyer $buyer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:buyers,email,'.$buyer->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $buyer->name = $request->name;
        $buyer->email = $request->email;

        if ($request->hasFile('image')) {
            if ($buyer->image) {
                Storage::disk('public')->delete('buyers/'.$buyer->image);
            }

            $imagePath = $request->file('image')->store('buyers', 'public');
            $buyer->image = basename($imagePath);
        }

        $buyer->save();

        return redirect()->route('buyer.index')->with('success', 'Buyer updated successfully.');
    }

    public function destroy(Buyer $buyer)
    {
        if ($buyer->image) {
            Storage::disk('public')->delete('buyers/'.$buyer->image);
        }

        $buyer->delete();

        return redirect()->route('buyer.index')->with('success', 'Buyer deleted successfully.');
    }
}
