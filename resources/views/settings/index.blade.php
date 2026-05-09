@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Manage workspace preferences and account settings.</p>
    </div>
</div>

<div class="settings-layout">
    <div class="card settings-card">
        <div class="settings-card-head">
            <div class="settings-icon">WS</div>

            <div>
                <h2>Workspace</h2>
                <p>Basic workspace information for ResolveIQ.</p>
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

            <div class="form-group full">
                <label>Workspace Description</label>
                <textarea rows="4" readonly>AI-powered helpdesk workspace for managing tickets, agents, departments, and support workflows.</textarea>
            </div>
        </div>
    </div>

    <div class="card settings-card">
        <div class="settings-card-head">
            <div class="settings-icon settings-icon-purple">UI</div>

            <div>
                <h2>Interface</h2>
                <p>Theme and interface preferences.</p>
            </div>
        </div>

        <div class="settings-options">
            <div class="settings-option">
                <div>
                    <strong>Light / Dark Mode</strong>
                    <span>Controlled from the top navigation switch.</span>
                </div>

                <span class="settings-pill">Enabled</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Animations</strong>
                    <span>Buttons, cards, and page transitions are active.</span>
                </div>

                <span class="settings-pill">Active</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Responsive Layout</strong>
                    <span>Dashboard adapts to tablets and mobile screens.</span>
                </div>

                <span class="settings-pill">Ready</span>
            </div>
        </div>
    </div>

    <div class="card settings-card">
        <div class="settings-card-head">
            <div class="settings-icon settings-icon-green">AI</div>

            <div>
                <h2>AI Features</h2>
                <p>AI assistant configuration status.</p>
            </div>
        </div>

        <div class="settings-options">
            <div class="settings-option">
                <div>
                    <strong>AI Assistant Page</strong>
                    <span>Generate summaries, suggested replies, and internal notes.</span>
                </div>

                <span class="settings-pill">Demo</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>Use as Reply</strong>
                    <span>Generated text can be saved directly into ticket replies.</span>
                </div>

                <span class="settings-pill">Enabled</span>
            </div>

            <div class="settings-option">
                <div>
                    <strong>External AI API</strong>
                    <span>Not connected yet. This can be added later.</span>
                </div>

                <span class="settings-pill muted-pill">Later</span>
            </div>
        </div>
    </div>
</div>
@endsection
