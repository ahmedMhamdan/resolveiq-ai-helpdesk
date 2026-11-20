@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<main class="auth-page auth-with-navbar">
    <section class="auth-card">
        <div class="auth-head">
            <h1>Verify your email</h1>
            <p>
                We sent a verification link to your email address.
                Please verify your email before using tickets.
            </p>
        </div>

        @if (session('success'))
            <div class="verify-success-text">
                {{ session('success') }}
            </div>
        @endif

        @if (session('status') === 'verification-link-sent')
            <div class="verify-success-text">
                A new verification link has been sent to your email address.
            </div>
        @endif

        <div class="auth-form">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <button type="submit" class="btn btn-primary auth-submit">
                    Resend Verification Email
                </button>
            </form>

            <div class="auth-bottom-link">
                Already verified?
                <a href="{{ route('dashboard') }}">Back to Dashboard</a>
            </div>
        </div>
    </section>
</main>
@endsection
