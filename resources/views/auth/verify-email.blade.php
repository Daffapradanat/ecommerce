@extends('layouts')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A new verification code has been sent to your email address.') }}
                        </div>
                    @endif

                    {{ __('Please check your email for the verification code and enter it below.') }}

                    <form method="POST" action="{{ route('verification.verify') }}" class="mt-3">
                        @csrf
                        <div class="form-group">
                            <input id="verification_code" type="text" class="form-control @error('verification_code') is-invalid @enderror" name="verification_code" required autocomplete="verification_code" autofocus placeholder="Enter verification code">
                            @error('verification_code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">
                            {{ __('Verify Email') }}
                        </button>
                    </form>

                    <form class="d-inline mt-3" method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
