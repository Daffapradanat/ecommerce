<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notifications\NewBuyer;
use App\Models\User;
use App\Models\Buyer;
use App\Mail\PasswordResetCodeMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:buyers',
            'password' => 'required|string|min:8',
        ]);

        $buyer = Buyer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_token' => Str::random(60),
        ]);

        $this->sendVerificationEmail($buyer);

        $users = User::all();
        foreach ($users as $user) {
            $user->notify(new NewBuyer($buyer));
        }

        return response()->json([
            'message' => __('messages.registration_successful'),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $buyer = Buyer::where('email', $request->email)->first();

        if (!$buyer || !Hash::check($request->password, $buyer->password)) {
            return response()->json([
                'message' => __('messages.invalid_credentials')
            ], 401);
        }

        if (!$buyer->email_verified_at) {
            return response()->json([
                'message' => __('messages.verify_email_before_login')
            ], 403);
        }

        $token = $buyer->createToken('auth_token')->plainTextToken;

        return response()->json([
            'buyer' => $buyer,
            'token' => $token,
        ]);
    }

    public function verifyEmail($token)
    {
        $buyer = Buyer::where('email_verification_token', $token)->first();

        if (!$buyer) {
            return response()->json([
                'message' => __('messages.invalid_verification_token')
            ], 400);
        }

        $buyer->email_verified_at = now();
        $buyer->email_verification_token = null;
        $buyer->save();

        return response()->json([
            'message' => __('messages.email_verified_successfully')
        ]);
    }

    private function sendVerificationEmail($buyer)
    {
        $verificationUrl = url("/api/verify-email/{$buyer->email_verification_token}");

        Mail::raw("Please click the following link to verify your email: {$verificationUrl}", function ($message) use ($buyer) {
            $message->to($buyer->email)
                ->subject('Verify Your Email Address');
        });
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $buyer = Buyer::where('email', $request->email)->first();

        if (!$buyer) {
            return response()->json(['message' => __('messages.no_account_found')], 404);
        }

        $code = Str::random(6);
        $buyer->password_reset_code = $code;
        $buyer->password_reset_code_expires_at = now()->addMinutes(15);
        $buyer->save();

        Mail::to($buyer->email)->send(new PasswordResetCodeMail($code));

        return response()->json(['message' => __('messages.password_reset_code_sent')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|min:8|confirmed',
        ]);

        $buyer = Buyer::where('email', $request->email)
            ->where('password_reset_code', $request->code)
            ->where('password_reset_code_expires_at', '>', now())
            ->first();

        if (!$buyer) {
            return response()->json(['message' => __('messages.invalid_reset_code')], 400);
        }

        $buyer->password = Hash::make($request->password);
        $buyer->password_reset_code = null;
        $buyer->password_reset_code_expires_at = null;
        $buyer->save();

        return response()->json(['message' => __('messages.password_reset_successful')]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('messages.logout_successful')]);
    }
}
