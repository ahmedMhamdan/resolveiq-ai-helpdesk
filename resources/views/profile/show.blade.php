@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Profile</h1>
        <p class="page-subtitle">Manage your account information and workspace activity.</p>
    </div>

    <a href="{{ route('profile.edit') }}" class="btn btn-edit-soft">
        Edit Profile
    </a>
</div>

@php
    $profileAvatarUrl = '';

    if ($user->avatar_path) {
        $profileAvatarUrl = method_exists($user, 'avatarUrl')
            ? $user->avatarUrl()
            : (str_starts_with($user->avatar_path, 'images/')
                ? asset($user->avatar_path)
                : asset('storage/' . $user->avatar_path));
    }

    $profileInitial = strtoupper(substr($user->name ?? 'U', 0, 1));
@endphp

<div class="profile-layout">
    <div class="card profile-main-card">
        <div class="profile-header">
            <div class="profile-avatar {{ $profileAvatarUrl ? 'has-image' : '' }}">
                @if ($profileAvatarUrl)
                    <img src="{{ $profileAvatarUrl }}" alt="{{ $user->name }} avatar" class="profile-avatar-img">
                @else
                    {{ $profileInitial }}
                @endif
            </div>

            <div>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>

                <span class="profile-role">
                    {{ ucfirst($user->role?->name ?? 'User') }}
                </span>
            </div>
        </div>

        <div class="profile-stats">
            <div class="profile-stat-box profile-stat-assigned">
                <span>Assigned Tickets</span>
                <strong>{{ $assignedTicketsCount }}</strong>
            </div>

            <div class="profile-stat-box profile-stat-replies">
                <span>Replies</span>
                <strong>{{ $repliesCount }}</strong>
            </div>

            <div class="profile-stat-box profile-stat-member">
                <span>Member Since</span>
                <strong>{{ $user->created_at->format('M Y') }}</strong>
            </div>
        </div>
    </div>

    <div class="card profile-info-card">
        <h2>Account Details</h2>

        <div class="detail-row">
            <small>Name</small>
            <strong>{{ $user->name }}</strong>
        </div>

        <div class="detail-row">
            <small>Email</small>
            <strong>{{ $user->email }}</strong>
        </div>

        <div class="detail-row">
            <small>Role</small>
            <strong>{{ ucfirst($user->role?->name ?? 'User') }}</strong>
        </div>

        <div class="detail-row">
            <small>Status</small>
            <strong>Active</strong>
        </div>
    </div>
</div>

<div class="card profile-activity-card">
    <div class="table-head">
        <div>
            <h2>Latest Replies</h2>
            <p class="page-subtitle">Recent replies written by this user.</p>
        </div>
    </div>

    <div class="activity-list">
        @forelse ($latestReplies as $reply)
            <div class="activity-item">
                <div class="activity-dot"></div>

                <div class="activity-content">
                    <strong>
                        {{ $reply->is_internal_note ? 'Internal note' : 'Reply' }}
                    </strong>

                    <span>
                        @if ($reply->ticket)
                            #{{ $reply->ticket->ticket_number }}
                        @else
                            Ticket removed
                        @endif
                        — {{ Str::limit($reply->message, 90) }}
                    </span>
                </div>

                <small>{{ $reply->created_at->diffForHumans() }}</small>
            </div>
        @empty
            <div class="empty">
                No replies yet.
            </div>
        @endforelse
    </div>
</div>
@endsection
