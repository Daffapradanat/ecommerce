<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

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
            'image_type' => 'required|in:upload,url',
            'image' => 'required_if:image_type,upload|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'required_if:image_type,url',  // Remove the 'url' validation
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        if ($request->image_type === 'upload' && $request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
            $user->image = basename($imagePath);
        } elseif ($request->image_type === 'url' && $request->filled('image_url')) {
            if (!$this->isValidImageUrl($request->image_url)) {
                return redirect()->back()->withInput()->with('error', 'The provided URL does not seem to be a valid image. Please check the URL and try again.');
            }
            $user->image = $request->image_url;
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User created successfully.');
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
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'image_type' => 'required|in:keep,upload,url',
            'image' => 'required_if:image_type,upload|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'required_if:image_type,url', 
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->image_type === 'upload' && $request->hasFile('image')) {
            if ($user->image && !filter_var($user->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete('users/'.$user->image);
            }
            $imagePath = $request->file('image')->store('users', 'public');
            $user->image = basename($imagePath);
        } elseif ($request->image_type === 'url' && $request->filled('image_url')) {
            if (!$this->isValidImageUrl($request->image_url)) {
                return redirect()->back()->withInput()->with('error', 'The provided URL is not a valid image.');
            }
            if ($user->image && !filter_var($user->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete('users/'.$user->image);
            }
            $user->image = $request->image_url;
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

    private function isValidImageUrl($url)
    {
        try {
            $response = Http::get($url);
            $contentType = $response->header('Content-Type');
            return str_starts_with($contentType, 'image/');
        } catch (\Exception $e) {
            return false;
        }
    }
}
