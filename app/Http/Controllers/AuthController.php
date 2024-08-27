<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    /**
     * Display a registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Store a new user.
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'email' => ['required', 'email', 'max:250', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $verificationCode = Str::random(6);

        $userRole = Role::where('name', 'user')->first();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'verification_code' => $verificationCode,
            'role_id' => $userRole->id,
        ]);

        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')->with('success', 'Please check your email for the verification code.');
    }

    public function verificationNotice()
    {
        if (Auth::check() && Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No user found with this email address.']);
        }

        if ($user->verification_code === $request->verification_code) {
            $user->markEmailAsVerified();
            $user->verification_code = null;
            $user->save();

            Auth::login($user);

            return redirect()->route('home')->with('success', 'Your email has been verified! You are now logged in.');
        }

        return back()->withErrors(['verification_code' => 'The verification code is invalid.']);
    }

    public function resendVerificationCode()
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $user->verification_code = Str::random(6);
        $user->save();

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'A new verification code has been sent to your email address.');
    }

    /**
     * Display a login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Authenticate the user.
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return redirect()->route('verification.notice')
                    ->withErrors(['email' => 'You need to verify your email first.']);
            }

            $request->session()->regenerate();
            return redirect()->intended('home');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function layouts()
    {
        return view('layouts');
    }

    public function showChangeEmailForm()
    {
        return view('auth.change-email');
    }

    public function changeEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:250', 'unique:users'],
        ]);

        $user = Auth::user();
        $verificationCode = Str::random(6);

        $user->new_email = $request->email;
        $user->email_change_verification_code = $verificationCode;
        $user->save();

        $user->sendEmailChangeVerificationNotification();

        return redirect()->route('email.change.verify')->with('success', 'Please check your new email for the verification code.');
    }

    public function showVerifyEmailChangeForm()
    {
        return view('auth.verify-email-change');
    }

    public function verifyEmailChange(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string',
        ]);

        $user = Auth::user();

        if ($user->email_change_verification_code === $request->verification_code) {
            $user->email = $user->new_email;
            $user->new_email = null;
            $user->email_change_verification_code = null;
            $user->email_verified_at = now();
            $user->save();

            return redirect()->route('home')->with('success', 'Your email has been changed and verified successfully.');
        }

        return back()->withErrors(['verification_code' => 'The verification code is invalid.']);
    }

        public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Log out the user from application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have logged out successfully!');
    }
}
