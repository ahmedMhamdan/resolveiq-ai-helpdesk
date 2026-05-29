@extends('layouts.app')

@section('title', __('profile.title'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('profile.title') }}</h1>
        <p class="page-subtitle">{{ __('profile.show_subtitle') }}</p>
    </div>

    <a href="{{ route('profile.edit') }}" class="btn btn-edit-soft">
        {{ __('profile.edit_profile') }}
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

@endphp

<div class="profile-layout">
    <div class="card profile-main-card">
        <div class="profile-header">
            <div class="profile-avatar {{ $profileAvatarUrl ? 'has-image' : '' }}">
                @if ($profileAvatarUrl)
                    <img src="{{ $profileAvatarUrl }}" alt="{{ $user->name }} avatar" class="profile-avatar-img">
                @else
                    <span class="avatar-fallback">?</span>
                @endif
            </div>

            <div>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>

                <span class="profile-role">
                    {{ ucfirst($user->role?->name ?? __('profile.default_role')) }}
                </span>
            </div>
        </div>

        <div class="profile-stats">
            <div class="profile-stat-box profile-stat-assigned">
                <span>{{ __('profile.assigned_tickets') }}</span>
                <strong>{{ $assignedTicketsCount }}</strong>
            </div>

            <div class="profile-stat-box profile-stat-replies">
                <span>{{ __('profile.replies') }}</span>
                <strong>{{ $repliesCount }}</strong>
            </div>

            <div class="profile-stat-box profile-stat-member">
                <span>{{ __('profile.member_since') }}</span>
                <strong>{{ $user->created_at->format('M Y') }}</strong>
            </div>
        </div>
    </div>

    <div class="card profile-info-card">
        <h2>{{ __('profile.account_details') }}</h2>

        <div class="detail-row">
            <small>{{ __('profile.name_label') }}</small>
            <strong>{{ $user->name }}</strong>
        </div>

        <div class="detail-row">
            <small>{{ __('profile.email_label') }}</small>
            <strong>{{ $user->email }}</strong>
        </div>

        <div class="detail-row">
            <small>{{ __('profile.role') }}</small>
            <strong>{{ ucfirst($user->role?->name ?? __('profile.default_role')) }}</strong>
        </div>

        <div class="detail-row">
            <small>{{ __('profile.status') }}</small>
            <strong>{{ __('profile.active') }}</strong>
        </div>
    </div>
</div>

<div class="card profile-activity-card">
    <div class="table-head">
        <div>
            <h2>{{ __('profile.latest_replies') }}</h2>
            <p class="page-subtitle">{{ __('profile.latest_replies_subtitle') }}</p>
        </div>
    </div>

    <div class="activity-list">
        @forelse ($latestReplies as $reply)
            <div class="activity-item">
                <div class="activity-dot"></div>

                <div class="activity-content">
                    <strong>
                        {{ $reply->is_internal_note ? __('profile.internal_note') : __('profile.reply') }}
                    </strong>

                    <span>
                        @if ($reply->ticket)
                            #{{ $reply->ticket->ticket_number }}
                        @else
                            {{ __('common.ticket_removed') }}
                        @endif
                        — {{ Str::limit($reply->message, 90) }}
                    </span>
                </div>

                <small>{{ $reply->created_at->diffForHumans() }}</small>
            </div>
        @empty
            <div class="empty">
                {{ __('profile.no_replies') }}
            </div>
        @endforelse
    </div>
</div>
@endsection