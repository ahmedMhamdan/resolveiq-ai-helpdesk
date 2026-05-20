@extends('layouts.app')

@section('title', 'Settings')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();
    $roleName = $currentUser?->role?->name ? ucfirst($currentUser->role->name) : 'User';
    $emailVerified = $currentUser?->hasVerifiedEmail();
    $environment = ucfirst(app()->environment());
    $appUrl = config('app.url');
    $mailDriver = config('mail.default', 'smtp');
    $aiProvider = config('services.ai_provider') ?: env('AI_PROVIDER', 'mock');
    $aiModel = config('services.openrouter.model') ?: env('OPENROUTER_MODEL', 'Not configured');
    $avatarStatus = $currentUser?->avatar_path ? 'Configured' : 'Not set';

    $workspaceStats = [
        ['label' => 'Environment', 'value' => $environment, 'tone' => app()->environment('production') ? 'success' : 'warning'],
        ['label' => 'Email', 'value' => $emailVerified ? 'Verified' : 'Pending', 'tone' => $emailVerified ? 'success' : 'warning'],
        ['label' => 'Role', 'value' => $roleName, 'tone' => 'primary'],
    ];
@endphp

<div class="settings-modern-page">
    <section class="settings-hero-card card">
        <div class="settings-hero-content">
            <div class="settings-hero-avatar">
                @if ($currentUser?->avatar_path)
                    <img src="{{ asset('storage/' . $currentUser->avatar_path) }}" alt="{{ $currentUser->name }}">
                @else
                    <span>{{ strtoupper(substr($currentUser?->name ?? 'U', 0, 1)) }}</span>
                @endif
            </div>

            <div class="settings-hero-copy">
                <span class="settings-kicker">Workspace Settings</span>
                <h1>ResolveIQ Control Center</h1>
                <p>
                    Manage account readiness, security status, platform services, and deployment checks from one clean workspace.
                </p>

                <div class="settings-hero-pills">
                    <span class="settings-status-pill is-primary">{{ $roleName }}</span>
                    <span class="settings-status-pill {{ $emailVerified ? 'is-success' : 'is-warning' }}">
                        {{ $emailVerified ? 'Email verified' : 'Email pending' }}
                    </span>
                    <span class="settings-status-pill {{ app()->environment('production') ? 'is-success' : 'is-muted' }}">
                        {{ $environment }}
                    </span>
                </div>
            </div>
        </div>

        <div class="settings-hero-actions">
            @if (Route::has('profile.edit'))
                <a href="{{ route('profile.edit') }}" class="btn btn-edit-soft">Edit Profile</a>
            @endif

            @if (Route::has('notifications.index'))
                <a href="{{ route('notifications.index') }}" class="btn btn-secondary">Notifications</a>
            @endif

            @if (Route::has('ai.index'))
                <a href="{{ route('ai.index') }}" class="btn btn-secondary">AI Assistant</a>
            @endif
        </div>
    </section>

    <section class="settings-stat-grid">
        @foreach ($workspaceStats as $stat)
            <div class="settings-stat-card card">
                <span>{{ $stat['label'] }}</span>
                <strong>{{ $stat['value'] }}</strong>
                <em class="settings-dot is-{{ $stat['tone'] }}"></em>
            </div>
        @endforeach
    </section>

    <section class="settings-modern-grid">
        <div class="card settings-modern-card settings-wide-card">
            <div class="settings-card-title">
                <span class="settings-card-icon">WS</span>
                <div>
                    <h2>Workspace Overview</h2>
                    <p>Core project information used by the ResolveIQ helpdesk.</p>
                </div>
            </div>

            <div class="settings-info-grid">
                <div class="settings-info-item">
                    <span>Workspace Name</span>
                    <strong>ResolveIQ Helpdesk</strong>
                </div>

                <div class="settings-info-item">
                    <span>Application URL</span>
                    <strong>{{ $appUrl }}</strong>
                </div>

                <div class="settings-info-item">
                    <span>Support Email</span>
                    <strong>support@resolveiq.test</strong>
                </div>

                <div class="settings-info-item">
                    <span>Workspace Type</span>
                    <strong>AI-assisted support desk</strong>
                </div>
            </div>

            <div class="settings-description-box">
                ResolveIQ centralizes support tickets, agent assignment, activity tracking, notifications, knowledge base context,
                and AI-assisted drafting for a realistic helpdesk workflow.
            </div>
        </div>

        <div class="card settings-modern-card">
            <div class="settings-card-title">
                <span class="settings-card-icon is-green">AC</span>
                <div>
                    <h2>Account</h2>
                    <p>Your profile and access status.</p>
                </div>
            </div>

            <div class="settings-status-list">
                <div class="settings-status-row">
                    <span>Name</span>
                    <strong>{{ $currentUser?->name ?? 'Unknown user' }}</strong>
                </div>

                <div class="settings-status-row">
                    <span>Email</span>
                    <strong>{{ $currentUser?->email ?? 'No email' }}</strong>
                </div>

                <div class="settings-status-row">
                    <span>Role</span>
                    <strong>{{ $roleName }}</strong>
                </div>

                <div class="settings-status-row">
                    <span>Avatar</span>
                    <strong>{{ $avatarStatus }}</strong>
                </div>
            </div>
        </div>

        <div class="card settings-modern-card">
            <div class="settings-card-title">
                <span class="settings-card-icon is-purple">AI</span>
                <div>
                    <h2>AI Services</h2>
                    <p>Assistant configuration and output safeguards.</p>
                </div>
            </div>

            <div class="settings-status-list">
                <div class="settings-status-row">
                    <span>Provider</span>
                    <strong>{{ ucfirst($aiProvider) }}</strong>
                </div>

                <div class="settings-status-row">
                    <span>Model</span>
                    <strong>{{ $aiModel }}</strong>
                </div>

                <div class="settings-status-row">
                    <span>Fallback Mode</span>
                    <strong>Enabled</strong>
                </div>

                <div class="settings-status-row">
                    <span>Knowledge Context</span>
                    <strong>Available</strong>
                </div>
            </div>
        </div>

        <div class="card settings-modern-card">
            <div class="settings-card-title">
                <span class="settings-card-icon is-orange">SEC</span>
                <div>
                    <h2>Security</h2>
                    <p>Authentication and verification status.</p>
                </div>
            </div>

            <div class="settings-check-list">
                <div class="settings-check-item">
                    <span class="{{ $emailVerified ? 'is-ok' : 'is-warn' }}"></span>
                    <div>
                        <strong>Email verification</strong>
                        <p>{{ $emailVerified ? 'Your email is verified and protected routes are available.' : 'Verify your email to unlock protected workflows.' }}</p>
                    </div>
                </div>

                <div class="settings-check-item">
                    <span class="is-ok"></span>
                    <div>
                        <strong>Role-based access</strong>
                        <p>Admin, agent, and user permissions control dashboard actions.</p>
                    </div>
                </div>

                <div class="settings-check-item">
                    <span class="is-ok"></span>
                    <div>
                        <strong>Sanctum API tokens</strong>
                        <p>API access is protected through token-based authentication.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card settings-modern-card">
            <div class="settings-card-title">
                <span class="settings-card-icon is-cyan">NT</span>
                <div>
                    <h2>Notifications</h2>
                    <p>Workspace alerts and ticket updates.</p>
                </div>
            </div>

            <div class="settings-status-list">
                <div class="settings-status-row">
                    <span>Ticket updates</span>
                    <strong>Enabled</strong>
                </div>

                <div class="settings-status-row">
                    <span>Unread counter</span>
                    <strong>Enabled</strong>
                </div>

                <div class="settings-status-row">
                    <span>Email mailer</span>
                    <strong>{{ ucfirst($mailDriver) }}</strong>
                </div>
            </div>
        </div>

        <div class="card settings-modern-card settings-wide-card">
            <div class="settings-card-title">
                <span class="settings-card-icon is-dark">DEP</span>
                <div>
                    <h2>Deployment Readiness</h2>
                    <p>Final checks before pushing ResolveIQ to production.</p>
                </div>
            </div>

            <div class="settings-deploy-grid">
                <div>
                    <strong>APP_DEBUG</strong>
                    <span>{{ config('app.debug') ? 'Enabled locally' : 'Disabled' }}</span>
                </div>

                <div>
                    <strong>Storage Link</strong>
                    <span>Required for avatars and attachments</span>
                </div>

                <div>
                    <strong>SMTP</strong>
                    <span>Required for verification emails</span>
                </div>

                <div>
                    <strong>Secrets</strong>
                    <span>Stored in .env, never shown here</span>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
