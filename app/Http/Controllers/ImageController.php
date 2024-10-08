<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function index()
    {
        $images = Image::all();

        return view('image.index', compact('images'));
    }

    public function create()
    {
        return view('image.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('image', $fileName, 'public');

            $image = Image::create([
                'name' => $request->name,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return redirect()->route('image.show', $image->id)->with('success', 'Image uploaded successfully');
        }

        return back()->with('error', 'Image upload failed');
    }

    public function show(Image $image)
    {
        return view('image.show', compact('image'));
    }

    public function edit(Image $image)
    {
        return view('image.edit', compact('image'));
    }

    public function update(Request $request, Image $image)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $image->update([
            'name' => $request->name,
        ]);

        return redirect()->route('image.show', $image->id)->with('success', 'Image updated successfully');
    }

    public function destroy(Image $image)
    {
        Storage::disk('public')->delete($image->file_path);
        $image->delete();

        return redirect()->route('image.index')->with('success', 'Image deleted successfully');
    }
}
