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
            'image_url' => [
                'required_if:image_type,url',
                'url',
                'active_url',
                function ($attribute, $value, $fail) {
                    $parsedUrl = parse_url($value);
                    $path = $parsedUrl['path'] ?? '';
                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $fail('The image URL must directly link to a jpg, jpeg, png, or gif file.');
                    }

                    $headers = get_headers($value, 1);
                    if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'image/') !== 0) {
                        // $fail('The URL does not point to a valid image file.');
                    }
                },
            ],
        ], [
            'image.required_if' => 'Please upload an image file.',
            'image_url.required_if' => 'Please provide a valid image URL.',
            'image_url.url' => 'The image URL must be a valid URL.',
            'image_url.active_url' => 'The image URL must be an active and accessible URL.',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        if ($request->image_type === 'upload' && $request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
            $user->image = basename($imagePath);
        } elseif ($request->image_type === 'url' && $request->filled('image_url')) {
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
            'image_url' => [
                'required_if:image_type,url',
                'url',
                'active_url',
                function ($attribute, $value, $fail) {
                    $parsedUrl = parse_url($value);
                    $path = $parsedUrl['path'] ?? '';
                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $fail('The image URL must directly link to a jpg, jpeg, png, or gif file.');
                    }

                    $headers = get_headers($value, 1);
                    if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'image/') !== 0) {
                        // $fail('The URL does not point to a valid image file.');
                    }
                },
            ],
        ], [
            'image_url.required_if' => 'Please provide a valid image URL.',
            'image_url.url' => 'The image URL format is invalid.',
            'image_url.active_url' => 'The image URL is not accessible.',
            'image_url.active_url' => 'The image URL must be an active and accessible URL.',
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
}
