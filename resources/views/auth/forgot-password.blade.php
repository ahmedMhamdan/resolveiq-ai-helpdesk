@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<main class="auth-page auth-with-navbar">
    <section class="auth-card auth-password-card">
        <div class="auth-head">
            <h1>Reset your password</h1>
            <p>Enter your email address and we will send you a secure password reset link.</p>
        </div>

        @if (session('status'))
            <div class="flash-message">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger auth-alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="auth-form">
            @csrf

            <div class="form-group full">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required
                    autofocus
                >
                <p class="auth-field-hint">
                    Use the same email you registered with in ResolveIQ.
                </p>
            </div>

            <button type="submit" class="btn btn-primary auth-submit">
                Send reset link
            </button>

            <div class="auth-bottom-link">
                Remember your password?
                <a href="{{ route('login') }}">Back to login</a>
            </div>
        </form>
    </section>
</main>
@endsection
