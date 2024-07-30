<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BuyerController extends Controller
{
    public function index(Request $request)
    {
        $query = Buyer::query();

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            });
        }

        $buyers = $query->paginate(10);

        return view('buyers.index', compact('buyers'));
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
