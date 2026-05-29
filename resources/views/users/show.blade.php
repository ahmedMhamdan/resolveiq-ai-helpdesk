@extends('layouts.app')

@section('title', __('users.title_show'))

@section('content')
@php
    $roleName = strtolower($user->role?->name ?? 'user');

    $avatarUrl = null;

    if ($user->avatar_path) {
        $avatarUrl = method_exists($user, 'avatarUrl')
            ? $user->avatarUrl()
            : (str_starts_with($user->avatar_path, 'images/')
                ? asset($user->avatar_path)
                : asset('storage/' . $user->avatar_path));
    }

    $createdTickets = $createdTickets ?? collect();
    $assignedTickets = $assignedTickets ?? collect();
    $recentReplies = $recentReplies ?? collect();
    $activityLogs = $activityLogs ?? collect();
@endphp

<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('users.title_show') }}</h1>
        <p class="page-subtitle">{{ __('users.show_subtitle') }}</p>
    </div>

    <div class="page-actions">
        @if ($roleName !== 'admin')
            <a href="{{ url('/users/' . $user->id . '/edit') }}" class="btn btn-primary">
                {{ __('users.edit_user_btn') }}
            </a>
        @endif

        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            {{ __('users.back') }}
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert flash-message" style="margin: 0 0 20px;">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger" style="margin: 0 0 20px;">
        {{ session('error') }}
    </div>
@endif

<div class="profile-layout">
    <div class="card profile-main-card">
        <div class="profile-header">
            <div class="profile-avatar">
                @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }} avatar">
                @else
                    <span class="avatar-fallback">?</span>
                @endif
            </div>

            <div>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
                <span class="profile-role role-badge role-{{ $roleName }}">
                    {{ ucfirst($roleName) }}
                </span>
            </div>
        </div>

        <div class="profile-stats">
            <div class="profile-stat-box profile-stat-assigned">
                <span>{{ __('users.created_tickets_heading') }}</span>
                <strong>{{ $user->tickets_count ?? 0 }}</strong>
            </div>

            <div class="profile-stat-box profile-stat-replies">
                <span>{{ __('users.assigned_tickets_heading') }}</span>
                <strong>{{ $user->assigned_tickets_count ?? 0 }}</strong>
            </div>

            <div class="profile-stat-box profile-stat-member">
                <span>{{ __('users.replies') }}</span>
                <strong>{{ $user->ticket_replies_count ?? 0 }}</strong>
            </div>
        </div>
    </div>

    <div class="card profile-info-card">
        <h2>{{ __('users.account_info') }}</h2>

        <div class="detail-row">
            <small>{{ __('users.role') }}</small>
            <strong>{{ ucfirst($roleName) }}</strong>
        </div>

        <div class="detail-row">
            <small>{{ __('users.email') }}</small>
            <strong>{{ $user->email }}</strong>
        </div>

        <div class="detail-row">
            <small>{{ __('users.joined') }}</small>
            <strong>{{ $user->created_at?->format('M d, Y') }}</strong>
        </div>

        <div class="detail-row">
            <small>{{ __('users.last_updated') }}</small>
            <strong>{{ $user->updated_at?->diffForHumans() }}</strong>
        </div>
    </div>
</div>

<div class="users-detail-grid">
    <div class="table-card">
        <div class="table-head">
            <div>
                <h2>{{ __('users.created_tickets_heading') }}</h2>
                <p class="page-subtitle">{{ __('users.show_subtitle') }}</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('users.ticket') }}</th>
                        <th>{{ __('users.status') }}</th>
                        <th>{{ __('users.priority') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($createdTickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="ticket-link">
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                    <span>{{ $ticket->title }}</span>
                                </a>
                            </td>
                            <td><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                            <td><span class="priority {{ $ticket->priority ?? 'unset' }}">{{ ucfirst($ticket->priority ?? __('common.not_set')) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty">{{ __('users.no_created_tickets') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card">
        <div class="table-head">
            <div>
                <h2>{{ __('users.assigned_tickets_heading') }}</h2>
                <p class="page-subtitle">{{ __('users.show_subtitle') }}</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('users.ticket') }}</th>
                        <th>{{ __('users.status') }}</th>
                        <th>{{ __('users.priority') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignedTickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="ticket-link">
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                    <span>{{ $ticket->title }}</span>
                                </a>
                            </td>
                            <td><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                            <td><span class="priority {{ $ticket->priority ?? 'unset' }}">{{ ucfirst($ticket->priority ?? __('common.not_set')) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty">{{ __('users.no_assigned_tickets') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="users-detail-grid">
    <div class="table-card">
        <div class="table-head">
            <div>
                <h2>{{ __('users.recent_replies') }}</h2>
                <p class="page-subtitle">{{ __('users.recent_replies_subtitle') }}</p>
            </div>
        </div>

        @forelse ($recentReplies as $reply)
            <div class="activity-item">
                <span class="activity-dot"></span>
                <div class="activity-content">
                    <strong>{{ $reply->ticket?->ticket_number ?? __('users.ticket') }}</strong>
                    <span>{{ str($reply->message)->limit(90) }}</span>
                </div>
                <small>{{ $reply->created_at?->diffForHumans() }}</small>
            </div>
        @empty
            <div class="empty">{{ __('users.no_replies') }}</div>
        @endforelse
    </div>

    <div class="table-card">
        <div class="table-head">
            <div>
                <h2>{{ __('users.recent_activity') }}</h2>
                <p class="page-subtitle">{{ __('users.recent_activity_subtitle') }}</p>
            </div>
        </div>

        @forelse ($activityLogs as $log)
            <div class="activity-item">
                <span class="activity-dot"></span>
                <div class="activity-content">
                    <strong>{{ $log->action }}</strong>
                    <span>{{ $log->ticket?->ticket_number ?? __('users.no_ticket_linked') }}</span>
                </div>
                <small>{{ $log->created_at?->diffForHumans() }}</small>
            </div>
        @empty
            <div class="empty">{{ __('users.no_activity') }}</div>
        @endforelse
    </div>
</div>
@endsection