@extends('layouts')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center font-weight-light my-4">{{ __('Verify Your Email Address') }}</h3>
                </div>
                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A new verification code has been sent to your email address.') }}
                        </div>
                    @endif

                    <p class="text-center mb-4">{{ __('Please check your email for the verification code and enter it below.') }}</p>

                    <form method="POST" action="{{ route('verification.verify') }}">
                        @csrf
                        <div class="form-floating mb-3">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Enter your email">
                            <label for="email">{{ __('messages.email_address') }}</label>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-floating mb-3">
                            <input id="verification_code" type="text" class="form-control @error('verification_code') is-invalid @enderror" name="verification_code" required autocomplete="verification_code" placeholder="Enter verification code">
                            <label for="verification_code">{{ __('messages.verification_code') }}</label>
                            @error('verification_code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Verify Email') }}
                            </button>
                            <form class="d-inline" method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-link">{{ __('Resend Code') }}</button>
                            </form>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small"><a href="{{ route('login') }}">{{ __('messages.already_verified') }}</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
