<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Imports\UsersImport;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            });
        }

        $users = $query->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $verificationCode = Str::random(6);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->verification_code = $verificationCode;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
            $user->image = basename($imagePath);
        }

        $user->save();

        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')->with('success', 'Administrator created. Please check the email for the verification code.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // 'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $request->name;
        // $user->email = $request->email;

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete('users/'.$user->image);
            }

            $imagePath = $request->file('image')->store('users', 'public');
            $user->image = basename($imagePath);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->image) {
            Storage::disk('public')->delete('users/'.$user->image);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function exportTemplate()
    {
        return Excel::download(new UsersExport(true), 'users_template.xlsx');
    }
}
