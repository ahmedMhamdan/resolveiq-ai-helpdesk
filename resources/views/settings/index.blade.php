@extends('layouts.app')

@section('title', 'Settings')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();
    $roleName = $currentUser?->role?->name ? ucfirst($currentUser->role->name) : 'User';
    $avatarStatus = $currentUser?->avatar_path ? 'Configured' : 'Not set';
    $environment = app()->environment();
@endphp

<div class="page-head settings-page-head">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Review workspace status, account preferences, AI features, notifications, and security settings.</p>
    </div>

    @if (Route::has('profile.edit'))
        <div class="page-actions">
            <a href="{{ route('profile.edit') }}" class="btn btn-edit-soft">Edit Profile</a>
        </div>
    @endif
</div>

<div class="settings-layout">
    <div class="card settings-card">
        <div class="settings-card-head">
            <div class="settings-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M4 7.5C4 6.12 5.12 5 6.5 5H17.5C18.88 5 20 6.12 20 7.5V16.5C20 17.88 18.88 19 17.5 19H6.5C5.12 19 4 17.88 4 16.5V7.5Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M8 9H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M8 13H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>

            <div>
                <h2>Workspace Overview</h2>
                <p>Basic system information for the ResolveIQ helpdesk workspace.</p>
            </div>
        </div>

        <div class="settings-form">
            <div class="form-group">
                <label>Workspace Name</label>
                <input type="text" value="ResolveIQ Helpdesk" readonly>
            </div>

            <div class="form-group">
                <label>Support Email</label>
                <input type="email" value="support@resolveiq.test" readonly>
            </div>

            <div class="form-group">
                <label>Environment</label>
                <input type="text" value="{{ ucfirst($environment) }}" readonly>
            </div>

            <div class="form-group">
                <label>Workspace Type</label>
                <input type="text" value="AI-powered support desk" readonly>
            </div>

            <div class="form-group full">
                <label>Description</label>
                <textarea rows="4" readonly>ResolveIQ helps teams manage support tickets, assign agents, track replies and activity, use knowledge base articles, and generate AI-assisted responses.</textarea>
            </div>
        </div>
    </div>

    <div class="card settings-card">
        <div class="settings-card-head">
            <div class="settings-icon settings-icon-green" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M5 20C5 16.7 7.7 14 11 14H13C16.3 14 19 16.7 19 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>

            <div>
                <h2>Account & Profile</h2>
                <p>Your current account information and profile status.</p>
            </div>
        </div>

        <div class="settings-options">
            <div class="settings-option">
                <div>
                    <strong>Name</strong>
                    <span>{{ $currentUser?->name ?? 'Unknown user' }}</span>
                </div>
                <span class="settings-pill">Active</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Email</strong>
                    <span>{{ $currentUser?->email ?? 'No email available' }}</span>
                </div>
                <span class="settings-pill">Verified</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Role</strong>
                    <span>{{ $roleName }}</span>
                </div>
                <span class="settings-pill">{{ $roleName }}</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Profile Image</strong>
                    <span>Used in the topbar, profile page, ticket replies, and user lists.</span>
                </div>
                <span class="settings-pill {{ $currentUser?->avatar_path ? '' : 'muted-pill' }}">{{ $avatarStatus }}</span>
            </div>
        </div>
    </div>

    <div class="card settings-card">
        <div class="settings-card-head">
            <div class="settings-icon settings-icon-purple" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M4 6H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M4 12H14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M4 18H18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M17 10L18.2 12.8L21 14L18.2 15.2L17 18L15.8 15.2L13 14L15.8 12.8L17 10Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
            </div>

            <div>
                <h2>Interface Preferences</h2>
                <p>Visual behavior and layout options used across the dashboard.</p>
            </div>
        </div>

        <div class="settings-options">
            <div class="settings-option">
                <div>
                    <strong>Light / Dark Mode</strong>
                    <span>Controlled from the top navigation switch and saved in the browser.</span>
                </div>
                <span class="settings-pill">Enabled</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Responsive Layout</strong>
                    <span>Dashboard pages adapt to smaller screens and wide tables use horizontal scrolling.</span>
                </div>
                <span class="settings-pill">Ready</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Animations</strong>
                    <span>Cards, buttons, rows, modals, and AI loading states use lightweight transitions.</span>
                </div>
                <span class="settings-pill">Active</span>
            </div>
        </div>
    </div>

    <div class="card settings-card">
        <div class="settings-card-head">
            <div class="settings-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M18 9C18 6.24 15.76 4 13 4H11C8.24 4 6 6.24 6 9V12.8C6 13.5 5.72 14.17 5.22 14.66L4.5 15.38C3.95 15.93 4.34 16.88 5.12 16.88H18.88C19.66 16.88 20.05 15.93 19.5 15.38L18.78 14.66C18.28 14.17 18 13.5 18 12.8V9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M9.8 19C10.25 19.62 11.04 20 12 20C12.96 20 13.75 19.62 14.2 19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>

            <div>
                <h2>Notifications</h2>
                <p>Status of alerts and ticket event notifications.</p>
            </div>
        </div>

        <div class="settings-options">
            <div class="settings-option">
                <div>
                    <strong>In-app Notifications</strong>
                    <span>Users receive alerts for ticket changes inside the dashboard.</span>
                </div>
                <span class="settings-pill">Enabled</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Unread Dropdown</strong>
                    <span>The bell menu shows unread notifications only.</span>
                </div>
                <span class="settings-pill">Enabled</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Ticket Events</strong>
                    <span>Replies, assignments, status, priority, due date, close, and reopen events are supported.</span>
                </div>
                <span class="settings-pill">Active</span>
            </div>
        </div>
    </div>

    <div class="card settings-card">
        <div class="settings-card-head">
            <div class="settings-icon settings-icon-green" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3L13.8 8.2L19 10L13.8 11.8L12 17L10.2 11.8L5 10L10.2 8.2L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M19 15L19.8 17.2L22 18L19.8 18.8L19 21L18.2 18.8L16 18L18.2 17.2L19 15Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                </svg>
            </div>

            <div>
                <h2>AI Assistant</h2>
                <p>AI generation, fallback behavior, and knowledge base context.</p>
            </div>
        </div>

        <div class="settings-options">
            <div class="settings-option">
                <div>
                    <strong>AI Provider</strong>
                    <span>Configured through environment variables with mock fallback support.</span>
                </div>
                <span class="settings-pill">OpenRouter</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Knowledge Base Context</strong>
                    <span>Published articles can be used as supporting context for AI replies.</span>
                </div>
                <span class="settings-pill">Enabled</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Use as Reply</strong>
                    <span>Generated replies can be inserted into tickets without including internal KB notes.</span>
                </div>
                <span class="settings-pill">Enabled</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>API Keys</strong>
                    <span>Secrets are stored in .env and are never displayed in the dashboard.</span>
                </div>
                <span class="settings-pill muted-pill">Hidden</span>
            </div>
        </div>
    </div>

    <div class="card settings-card">
        <div class="settings-card-head">
            <div class="settings-icon settings-icon-purple" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3L19 6V11C19 15.7 16.15 18.85 12 21C7.85 18.85 5 15.7 5 11V6L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <div>
                <h2>Security & Access</h2>
                <p>Authentication and role-based access status.</p>
            </div>
        </div>

        <div class="settings-options">
            <div class="settings-option">
                <div>
                    <strong>Authentication</strong>
                    <span>Login and account flows are handled through Laravel Fortify.</span>
                </div>
                <span class="settings-pill">Fortify</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Role-based Access</strong>
                    <span>Admin, agent, and user permissions control ticket visibility and actions.</span>
                </div>
                <span class="settings-pill">Enabled</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Two-factor Columns</strong>
                    <span>The database is prepared for two-factor authentication support.</span>
                </div>
                <span class="settings-pill">Available</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Passkeys Table</strong>
                    <span>The project contains database support for passkey-based authentication.</span>
                </div>
                <span class="settings-pill">Available</span>
            </div>
        </div>
    </div>
</div>
@endsection
