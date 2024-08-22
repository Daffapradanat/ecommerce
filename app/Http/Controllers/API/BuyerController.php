<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class BuyerController extends Controller
{


    public function update(Request $request)
    {
        $buyer = Auth::user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('buyers')->ignore($buyer->id),
            ],
            'password' => 'sometimes|string|min:8|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->has('name')) {
            $buyer->name = $request->name;
        }

        if ($request->has('email') && $request->email !== $buyer->email) {
            $buyer->email = $request->email;
            $buyer->email_verified_at = null;
            $buyer->email_verification_token = Str::random(60);
            $this->sendVerificationEmail($buyer);
        }

        if ($request->has('password')) {
            $buyer->password = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            if ($buyer->image) {
                Storage::disk('public')->delete('buyers/'.$buyer->image);
            }

            $imagePath = $request->file('image')->store('buyers', 'public');
            $buyer->image = basename($imagePath);
        }

        $buyer->save();

        $imageUrl = $buyer->image ? url('storage/buyers/'.$buyer->image) : null;

        return response()->json([
            'message' => 'Profile updated successfully',
            'buyer' => array_merge($buyer->toArray(), ['image_url' => $imageUrl]),
        ], 200);
    }
}
