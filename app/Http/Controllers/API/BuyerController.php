<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BuyerController extends Controller
{

    public function update(Request $request)
    {
        $buyer = Auth::user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->has('name')) {
            $buyer->name = $request->name;
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

    public function requestEmailChange(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|unique:buyers,email'
        ]);

        $buyer = Auth::user();
        $code = Str::random(6);

        $buyer->email_change_code = $code;
        $buyer->email_change_new_email = $request->new_email;
        $buyer->email_change_code_expires_at = now()->addMinutes(15);
        $buyer->save();

        Mail::raw("Your email change verification code is: {$code}", function ($message) use ($request) {
            $message->to($request->new_email)
                ->subject('Email Change Verification Code');
        });

        return response()->json(['message' => 'Verification code sent to new email.']);
    }

    public function verifyEmailChange(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $buyer = Auth::user();

        if (!$buyer->email_change_code || !$buyer->email_change_new_email || $buyer->email_change_code_expires_at < now()) {
            return response()->json(['message' => 'Invalid or expired email change request.'], 400);
        }

        if ($request->code !== $buyer->email_change_code) {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        $oldEmail = $buyer->email;
        $newEmail = $buyer->email_change_new_email;

        $buyer->email = $newEmail;
        $buyer->email_verified_at = now();
        $buyer->email_change_code = null;
        $buyer->email_change_new_email = null;
        $buyer->email_change_code_expires_at = null;
        $buyer->save();

        return response()->json([
            'message' => 'Email changed successfully.',
            'old_email' => $oldEmail,
            'new_email' => $newEmail
        ], 200);
    }
}
