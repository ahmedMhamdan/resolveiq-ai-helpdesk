<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>ResolveIQ AI Helpdesk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">
</head>
<body>
    <main class="public-page">
        <nav class="public-nav">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark">R</span>
                <span class="brand-text">Resolve<span>IQ</span></span>
            </a>

            <div class="public-nav-actions">
                <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
            </div>
        </nav>

        <section class="public-hero">
            <div class="public-hero-content">
                <span class="public-badge">AI-powered Helpdesk Platform</span>

                <h1>
                    Manage support tickets smarter with
                    <span>ResolveIQ</span>
                </h1>

                <p>
                    ResolveIQ helps teams receive tickets, assign agents, track activity,
                    and prepare AI-assisted summaries, replies, and priority suggestions.
                </p>

                <div class="public-hero-actions">
                    <a href="{{ route('register') }}" class="btn btn-primary">Create Account</a>
                    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                </div>
            </div>

            <div class="public-preview-card card">
                <div class="public-preview-head">
                    <div>
                        <strong>Live Ticket Preview</strong>
                        <span>Support workflow overview</span>
                    </div>
                    <span class="badge open">Open</span>
                </div>

                <div class="public-ticket-row">
                    <div>
                        <strong>Login issue</strong>
                        <span>Waiting for agent response</span>
                    </div>
                    <span class="priority high">High</span>
                </div>

                <div class="public-ticket-row">
                    <div>
                        <strong>Billing question</strong>
                        <span>Needs admin review</span>
                    </div>
                    <span class="priority unset">Not set</span>
                </div>

                <div class="public-ticket-row">
                    <div>
                        <strong>Email update request</strong>
                        <span>Resolved successfully</span>
                    </div>
                    <span class="badge solved">Solved</span>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
