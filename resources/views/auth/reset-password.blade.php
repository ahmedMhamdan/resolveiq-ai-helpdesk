@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<main class="auth-page auth-with-navbar">
    <section class="auth-card auth-password-card">
        <div class="auth-head">
            <h1>Create new password</h1>
            <p>Choose a strong password to secure your ResolveIQ account.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger auth-alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="auth-form">
            @csrf

            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div class="form-group full">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', request()->email) }}"
                    placeholder="you@example.com"
                    required
                    autofocus
                >
            </div>

            <div class="form-group full">
                <label for="password">New password</label>
                <div class="password-input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter new password"
                        required
                    >
                    <button type="button" class="password-eye-toggle" data-password-toggle="password" aria-label="Show password">
                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 3l18 18"></path>
                            <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                            <path d="M9.88 4.24A10.6 10.6 0 0 1 12 4c6.5 0 10 8 10 8a16.1 16.1 0 0 1-3.19 4.28"></path>
                            <path d="M6.61 6.61A15.8 15.8 0 0 0 2 12s3.5 8 10 8a10.8 10.8 0 0 0 5.39-1.39"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group full">
                <label for="password_confirmation">Confirm new password</label>
                <div class="password-input-wrap">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirm new password"
                        required
                    >
                    <button type="button" class="password-eye-toggle" data-password-toggle="password_confirmation" aria-label="Show password">
                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 3l18 18"></path>
                            <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                            <path d="M9.88 4.24A10.6 10.6 0 0 1 12 4c6.5 0 10 8 10 8a16.1 16.1 0 0 1-3.19 4.28"></path>
                            <path d="M6.61 6.61A15.8 15.8 0 0 0 2 12s3.5 8 10 8a10.8 10.8 0 0 0 5.39-1.39"></path>
                        </svg>
                    </button>
                </div>
                <p class="auth-field-hint">
                    Use at least 8 characters. A mix of letters, numbers, and symbols is better.
                </p>
            </div>

            <button type="submit" class="btn btn-primary auth-submit">
                Reset password
            </button>

            <div class="auth-bottom-link">
                Already reset it?
                <a href="{{ route('login') }}">Back to login</a>
            </div>
        </form>
    </section>
</main>
@endsection
