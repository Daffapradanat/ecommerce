<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
<<<<<<< HEAD
=======
use App\Exports\BuyersExport;
use Maatwebsite\Excel\Facades\Excel;
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300

class BuyerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
<<<<<<< HEAD
            $query = Buyer::query();
=======
            $query = Buyer::query('status', 'active');
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300

            return DataTables::of($query)
                ->addColumn('image', function ($buyer) {
                    if ($buyer->image) {
<<<<<<< HEAD
                        return '<img src="' . asset('storage/buyers/' . $buyer->image) . '" alt="' . $buyer->name . '" class="rounded-circle" width="50" height="50" style="object-fit: cover;">';
=======
                        if (filter_var($buyer->image, FILTER_VALIDATE_URL)) {
                            return '<img src="' . $buyer->image . '" alt="' . $buyer->name . '" class="rounded-circle" width="50" height="50" style="object-fit: cover;">';
                        } else {
                            return '<img src="' . asset('storage/buyers/' . $buyer->image) . '" alt="' . $buyer->name . '" class="rounded-circle" width="50" height="50" style="object-fit: cover;">';
                        }
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
                    } else {
                        return '<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px;">' . strtoupper(substr($buyer->name, 0, 1)) . '</div>';
                    }
                })
                ->addColumn('action', function ($buyer) {
<<<<<<< HEAD
                    return '<div class="d-flex justify-content-start align-items-center">
                                <a href="' . route('buyer.show', $buyer->id) . '" class="btn btn-info btn-sm me-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm me-0" data-bs-toggle="modal" data-bs-target="#deleteModal' . $buyer->id . '">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>';
=======
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
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
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

<<<<<<< HEAD
        $buyer = new buyer();
=======
        $buyer = new Buyer();
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
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
<<<<<<< HEAD

=======
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
            $imagePath = $request->file('image')->store('buyers', 'public');
            $buyer->image = basename($imagePath);
        }

        $buyer->save();

        return redirect()->route('buyer.index')->with('success', 'Buyer updated successfully.');
    }

<<<<<<< HEAD
    public function destroy(Buyer $buyer)
    {
        if ($buyer->image) {
            Storage::disk('public')->delete('buyers/'.$buyer->image);
        }

        $buyer->delete();

        return redirect()->route('buyer.index')->with('success', 'Buyer deleted successfully.');
=======
        public function restore(Buyer $buyer)
    {
        if ($buyer->status === 'deleted') {
            $buyer->update(['status' => 'active']);
            return redirect()->route('buyer.show', $buyer->id)->with('success', 'Buyer restored successfully.');
        } else {
            return redirect()->route('buyer.show', $buyer->id)->with('error', 'Buyer is not deleted.');
        }
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

    public function export()
    {
        return Excel::download(new BuyersExport, 'buyers.xlsx');
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
    }
}
