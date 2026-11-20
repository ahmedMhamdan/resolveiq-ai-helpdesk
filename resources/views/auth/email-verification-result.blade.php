@extends('layouts.auth')

@section('title', $title)

@section('content')
<main class="auth-page auth-with-navbar">
    <section class="auth-card verification-result-card">
        <div class="verification-result-icon {{ $status === 'success' ? 'is-success' : 'is-error' }}">
            @if ($status === 'success')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
            @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                </svg>
            @endif
        </div>

        <div class="auth-head verification-result-head">
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
        </div>

        <a href="{{ $redirectUrl }}" class="btn btn-primary auth-submit verification-result-btn" data-redirect-after="3500">
            {{ $buttonText }}
        </a>

        <p class="verification-result-note">
            Redirecting automatically...
        </p>
    </section>
</main>
@endsection
