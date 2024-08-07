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
            $query = Buyer::query('status', 'active');

            return DataTables::of($query)
                ->addColumn('image', function ($buyer) {
                    if ($buyer->image) {
                        if (filter_var($buyer->image, FILTER_VALIDATE_URL)) {
                            return '<img src="' . $buyer->image . '" alt="' . $buyer->name . '" class="rounded-circle" width="50" height="50" style="object-fit: cover;">';
                        } else {
                            return '<img src="' . asset('storage/buyers/' . $buyer->image) . '" alt="' . $buyer->name . '" class="rounded-circle" width="50" height="50" style="object-fit: cover;">';
                        }
                    } else {
                        return '<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px;">' . strtoupper(substr($buyer->name, 0, 1)) . '</div>';
                    }
                })
                ->addColumn('action', function ($buyer) {
                    $viewBtn = '<a href="' . route('buyer.show', $buyer->id) . '" class="btn btn-info btn-sm me-2">
                                    <i class="fas fa-eye"></i>
                                </a>';

                    $deleteBtn = '';
                    if ($buyer->status !== 'deleted') {
                        $deleteBtn = '<button type="button" class="btn btn-danger btn-sm me-0 delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-buyer-id="' . $buyer->id . '">
                                        <i class="fas fa-trash"></i>
                                      </button>';
                    }

                    return '<div class="d-flex justify-content-start align-items-center">' . $viewBtn . $deleteBtn . '</div>';
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
            'image_type' => 'required|in:upload,url',
            'image' => 'required_if:image_type,upload|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'required_if:image_type,url|url',
        ]);

        $buyer = new Buyer();
        $buyer->name = $request->name;
        $buyer->email = $request->email;
        $buyer->password = Hash::make($request->password);

        if ($request->image_type === 'upload' && $request->hasFile('image')) {
            $imagePath = $request->file('image')->store('buyers', 'public');
            $buyer->image = basename($imagePath);
        } elseif ($request->image_type === 'url' && $request->filled('image_url')) {
            $buyer->image = $request->image_url;
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
            if ($buyer->image && !filter_var($buyer->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete('buyers/'.$buyer->image);
            }
            $imagePath = $request->file('image')->store('buyers', 'public');
            $buyer->image = basename($imagePath);
        } elseif ($request->filled('image_url')) {
            if ($buyer->image && !filter_var($buyer->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete('buyers/'.$buyer->image);
            }
            $buyer->image = $request->image_url;
        }

        $buyer->save();

        return redirect()->route('buyer.index')->with('success', 'Buyer updated successfully.');
    }

    public function destroy(Buyer $buyer)
    {
        if ($buyer->status !== 'deleted') {
            if ($buyer->image) {
                Storage::disk('public')->delete('buyers/'.$buyer->image);
            }

            $buyer->update(['status' => 'deleted']);

            return redirect()->route('buyer.index')->with('success', 'Buyer marked as deleted successfully.');
        } else {
            return redirect()->route('buyer.index')->with('error', 'Buyer is already deleted.');
        }
    }
}
