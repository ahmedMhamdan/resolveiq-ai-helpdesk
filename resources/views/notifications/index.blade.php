@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    @php
        $currentUser = auth()->user();
        $activeFilter = request('filter', 'all');

        $totalNotifications = $currentUser->notifications()->count();
        $unreadNotifications = $currentUser->unreadNotifications()->count();
        $readNotifications = max($totalNotifications - $unreadNotifications, 0);
    @endphp

    <div class="page-head notifications-page-head">
        <div>
            <h1>Notifications</h1>
            <p class="page-subtitle">Track ticket updates, assignments, replies, and due date changes.</p>
        </div>

        <div class="notifications-page-actions">
            @if ($unreadNotifications > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-primary">
                        Mark all as read
                    </button>
                </form>
            @endif

            @if ($readNotifications > 0)
                <form
                    action="{{ route('notifications.deleteRead') }}"
                    method="POST"
                    onsubmit="return confirm('Delete all read notifications?')"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-secondary">
                        Delete read
                    </button>
                </form>
            @endif

            @if ($totalNotifications > 0)
                <form
                    action="{{ route('notifications.deleteAll') }}"
                    method="POST"
                    onsubmit="return confirm('Delete all notifications? This action cannot be undone.')"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger-soft">
                        Delete all
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="notification-summary-grid">
        <a
            href="{{ route('notifications.index', ['filter' => 'all']) }}"
            class="notification-summary-card {{ $activeFilter === 'all' ? 'active' : '' }}"
        >
            <span>All</span>
            <strong>{{ $totalNotifications }}</strong>
        </a>

        <a
            href="{{ route('notifications.index', ['filter' => 'unread']) }}"
            class="notification-summary-card {{ $activeFilter === 'unread' ? 'active' : '' }}"
        >
            <span>Unread</span>
            <strong>{{ $unreadNotifications }}</strong>
        </a>

        <a
            href="{{ route('notifications.index', ['filter' => 'read']) }}"
            class="notification-summary-card {{ $activeFilter === 'read' ? 'active' : '' }}"
        >
            <span>Read</span>
            <strong>{{ $readNotifications }}</strong>
        </a>
    </div>

    <section class="card notifications-card">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $type = strtoupper(substr($data['type'] ?? 'T', 0, 1));
            @endphp

            <div class="notification-item {{ $isUnread ? 'unread' : '' }}">
                <div class="notification-icon">
                    {{ $type }}
                </div>

                <div class="notification-content">
                    <div class="notification-head">
                        <strong>{{ $data['title'] ?? 'Notification' }}</strong>

                        @if ($isUnread)
                            <span class="notification-badge">New</span>
                        @else
                            <span class="notification-badge read">Read</span>
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

                    <form
                        action="{{ route('notifications.destroy', $notification) }}"
                        method="POST"
                        onsubmit="return confirm('Delete this notification?')"
                    >
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-sm btn-danger-soft">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty notification-empty-state">
                <strong>No notifications found.</strong>
                <span>
                    @if ($activeFilter === 'unread')
                        You have no unread notifications.
                    @elseif ($activeFilter === 'read')
                        You have no read notifications.
                    @else
                        Ticket updates will appear here once the system sends notifications.
                    @endif
                </span>
            </div>
        @endforelse

        @if ($notifications->hasPages())
            <div class="pagination">
                {{ $notifications->links('vendor.pagination.resolveiq') }}
            </div>
        @endif
    </section>
@endsection
