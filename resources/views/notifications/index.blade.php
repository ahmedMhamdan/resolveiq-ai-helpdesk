@extends('layouts.app')

@section('title', __('notifications.title'))

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
            <h1>{{ __('notifications.title') }}</h1>
            <p class="page-subtitle">{{ __('notifications.subtitle') }}</p>
        </div>

        <div class="notifications-page-actions">
            @if ($unreadNotifications > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-primary">
                        {{ __('notifications.mark_all_read') }}
                    </button>
                </form>
            @endif

            @if ($readNotifications > 0)
                <form
                    action="{{ route('notifications.deleteRead') }}"
                    method="POST"
                    onsubmit="return confirm('{{ __('notifications.confirm_delete_read') }}')"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-secondary">
                        {{ __('notifications.delete_read') }}
                    </button>
                </form>
            @endif

            @if ($totalNotifications > 0)
                <form
                    action="{{ route('notifications.deleteAll') }}"
                    method="POST"
                    onsubmit="return confirm('{{ __('notifications.confirm_delete_all') }}')"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger-soft">
                        {{ __('notifications.delete_all') }}
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
            <span>{{ __('notifications.all') }}</span>
            <strong>{{ $totalNotifications }}</strong>
        </a>

        <a
            href="{{ route('notifications.index', ['filter' => 'unread']) }}"
            class="notification-summary-card {{ $activeFilter === 'unread' ? 'active' : '' }}"
        >
            <span>{{ __('notifications.unread') }}</span>
            <strong>{{ $unreadNotifications }}</strong>
        </a>

        <a
            href="{{ route('notifications.index', ['filter' => 'read']) }}"
            class="notification-summary-card {{ $activeFilter === 'read' ? 'active' : '' }}"
        >
            <span>{{ __('notifications.read') }}</span>
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
                        <strong>{{ $data['title'] ?? __('notifications.notification') }}</strong>

                        @if ($isUnread)
                            <span class="notification-badge">{{ __('notifications.new') }}</span>
                        @else
                            <span class="notification-badge read">{{ __('notifications.read') }}</span>
                        @endif
                    </div>

                    <p>{{ $data['message'] ?? '' }}</p>

                    <small>
                        {{ $data['actor_name'] ?? __('notifications.system') }}
                        ·
                        {{ $notification->created_at?->diffForHumans() }}
                    </small>
                </div>

                <div class="notification-actions">
                    <form action="{{ route('notifications.read', $notification) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="btn btn-sm btn-primary">
                            {{ __('notifications.open') }}
                        </button>
                    </form>

                    <form
                        action="{{ route('notifications.destroy', $notification) }}"
                        method="POST"
                        onsubmit="return confirm('{{ __('notifications.confirm_delete_one') }}')"
                    >
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-sm btn-danger-soft">
                            {{ __('notifications.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty notification-empty-state">
                <strong>{{ __('notifications.no_notifications') }}</strong>
                <span>
                    @if ($activeFilter === 'unread')
                        {{ __('notifications.no_unread') }}
                    @elseif ($activeFilter === 'read')
                        {{ __('notifications.no_read') }}
                    @else
                        {{ __('notifications.no_notifications_desc') }}
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