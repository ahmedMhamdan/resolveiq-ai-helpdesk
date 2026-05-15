@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="page-head">
        <div>
            <h1>Notifications</h1>
            <p class="page-subtitle">Track ticket updates, assignments, and replies.</p>
        </div>

        @if (auth()->user()->unreadNotifications()->count())
            <form action="{{ route('notifications.readAll') }}" method="POST">
                @csrf
                @method('PATCH')

                <button type="submit" class="btn btn-primary">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    <section class="card notifications-card">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
            @endphp

            <div class="notification-item {{ $isUnread ? 'unread' : '' }}">
                <div class="notification-icon">
                    {{ strtoupper(substr($data['type'] ?? 'T', 0, 1)) }}
                </div>

                <div class="notification-content">
                    <div class="notification-head">
                        <strong>{{ $data['title'] ?? 'Notification' }}</strong>

                        @if ($isUnread)
                            <span class="notification-badge">New</span>
                        @endif
                    </div>

                    <p>{{ $data['message'] ?? '' }}</p>

                    <small>
                        {{ $data['actor_name'] ?? 'System' }}
                        ·
                        {{ $notification->created_at?->diffForHumans() }}
                    </small>
                </div>

                <div class="notification-actions">
                    <form action="{{ route('notifications.read', $notification) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="btn btn-sm btn-primary">
                            Open
                        </button>
                    </form>

                    <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-sm btn-danger-soft">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty">
                No notifications yet.
            </div>
        @endforelse

        <div class="pagination">
            {{ $notifications->links('vendor.pagination.resolveiq') }}
        </div>
    </section>
@endsection
