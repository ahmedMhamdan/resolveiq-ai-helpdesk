@extends('layouts.auth')

@section('title', 'Login')

@section('auth-action')
    @guest
        <a href="{{ route('register') }}" class="btn btn-secondary">Create account</a>
    @endguest
@endsection

@section('content')
<main class="auth-page auth-with-navbar">
    <div class="auth-login-layout">
    <section class="auth-card">
        <div class="auth-head">
            <h1>Welcome back</h1>
            <p>Sign in to your support workspace.</p>
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

        <form action="{{ route('login') }}" method="POST" class="auth-form">
            @csrf

            <div class="form-group full">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="admin@resolveiq.test"
                    required
                    autofocus
                >
            </div>

            <div class="form-group full">
                <label for="password">Password</label>
                <div class="password-input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
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

            <div class="auth-row">
                <label class="check-row auth-remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-forgot-link">
                        Forgot password?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary auth-submit">
                Login
            </button>

            @guest
                <div class="auth-bottom-link">
                    Don’t have an account?
                    <a href="{{ route('register') }}">Register</a>
                </div>
            @endguest
        </form>
    </section>
    <aside class="auth-card demo-panel" aria-labelledby="demo-heading">
        <h2 id="demo-heading">Try a demo account</h2>
        <p class="demo-intro">Open the workspace as an admin, agent, or customer.</p>
        <p class="demo-password">Password for all accounts: <code>password</code></p>

        <div class="demo-accounts">
            @foreach ([
                'Admin' => 'admin@resolveiq.test',
                'Agent' => 'agent@resolveiq.test',
                'Second Agent' => 'agent2@resolveiq.test',
                'User' => 'user@resolveiq.test',
                'Customer' => 'omar@resolveiq.test',
            ] as $demoRole => $demoEmail)
                <form action="{{ route('login') }}" method="POST" class="demo-account-form">
                    @csrf
                    <input type="hidden" name="email" value="{{ $demoEmail }}">
                    <input type="hidden" name="password" value="password">
                    <button type="submit" class="demo-account" aria-label="Sign in as {{ $demoRole }}">
                        <span class="demo-account-details">
                            <strong>{{ $demoRole }}</strong>
                            <span class="demo-email">{{ $demoEmail }}</span>
                        </span>
                        <span class="demo-action">Sign in <span aria-hidden="true">&rarr;</span></span>
                    </button>
                </form>
            @endforeach
        </div>
    </aside>
    </div>
</main>
@endsection
