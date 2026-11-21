@extends('layouts.app')

@section('title', __('dashboard.title'))

@section('content')
    @php
        $stats = $stats ?? [
            'open' => 0,
            'pending' => 0,
            'solved' => 0,
            'urgent' => 0,
        ];

        $latestTickets = $latestTickets ?? collect();
        $latestActivities = $latestActivities ?? collect();

        $currentUser = auth()->user();
        $role = strtolower($role ?? $currentUser?->role?->name ?? 'user');

        $dashboardTitle = $role === 'agent' ? __('dashboard.agent_dashboard') : ($role === 'user' ? __('dashboard.my_dashboard') : __('dashboard.title'));
        $dashboardSubtitle = $role === 'agent'
            ? __('dashboard.agent_subtitle')
            : ($role === 'user'
                ? __('dashboard.user_subtitle')
                : __('dashboard.admin_subtitle'));

        $ticketsTitle = $role === 'agent' ? __('dashboard.assigned_tickets') : ($role === 'user' ? __('dashboard.my_tickets') : __('dashboard.latest_tickets'));
        $ticketsSubtitle = $role === 'agent'
            ? __('dashboard.assigned_tickets_subtitle')
            : ($role === 'user'
                ? __('dashboard.my_tickets_subtitle')
                : __('dashboard.latest_tickets_subtitle'));

        $activitySubtitle = $role === 'admin'
            ? __('dashboard.activity_subtitle_admin')
            : __('dashboard.activity_subtitle_user');

        $avatarUrlFor = fn ($person) => $person?->avatarUrl();

    @endphp

    <div class="page-head dashboard-heading">
        <div>
            <h1 class="page-title">{{ $dashboardTitle }}</h1>
            <p class="page-subtitle">{{ $dashboardSubtitle }}</p>
        </div>

    </div>

    <section class="grid stats">
        <div class="card stat-card">
            <div class="stat-top">
                <span data-auto-translate>{{ __('dashboard.open_tickets') }}</span>
                <x-icon name="inbox" class="stat-icon" />
            </div>
            <div class="stat-number">{{ $stats['open'] ?? 0 }}</div>
            <div class="stat-trend" data-auto-translate>{{ __('dashboard.active_requests') }}</div>
        </div>

        <div class="card stat-card">
            <div class="stat-top">
                <span data-auto-translate>{{ __('dashboard.pending') }}</span>
                <x-icon name="clock" class="stat-icon" />
            </div>
            <div class="stat-number">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-trend" data-auto-translate>{{ __('dashboard.waiting_for_updates') }}</div>
        </div>

        <div class="card stat-card">
            <div class="stat-top">
                <span data-auto-translate>{{ __('dashboard.solved') }}</span>
                <x-icon name="check" class="stat-icon" />
            </div>
            <div class="stat-number">{{ $stats['solved'] ?? 0 }}</div>
            <div class="stat-trend" data-auto-translate>{{ __('dashboard.resolved_tickets') }}</div>
        </div>

        <div class="card stat-card">
            <div class="stat-top">
                <span data-auto-translate>{{ __('dashboard.urgent') }}</span>
                <x-icon name="flag" class="stat-icon" />
            </div>
            <div class="stat-number">{{ $stats['urgent'] ?? 0 }}</div>
            <div class="stat-trend" data-auto-translate>{{ __('dashboard.needs_attention') }}</div>
        </div>
    </section>

    <section class="card table-card dashboard-tickets-card">
        <div class="table-head dashboard-tickets-head">
            <div class="dashboard-tickets-heading">
                <h2>{{ $ticketsTitle }}</h2>
                <p class="page-subtitle">{{ $ticketsSubtitle }}</p>
            </div>

            <div class="dashboard-ticket-tools">
                <form method="GET" action="{{ route('tickets.index') }}" class="dashboard-ticket-search-form">
                    <div class="dashboard-ticket-search-box">
                        <span class="dashboard-ticket-search-icon" aria-hidden="true">
                            <x-icon name="search" />
                        </span>

                        <input
                            type="search"
                            name="search"
                            placeholder="{{ __('dashboard.search_placeholder') }}"
                            aria-label="{{ __('dashboard.search_placeholder') }}"
                            data-auto-translate-attribute="placeholder"
                        >

                        <button type="submit" class="dashboard-ticket-search-btn" data-auto-translate>
                            {{ __('dashboard.search') }}
                        </button>
                    </div>
                </form>

                <a class="btn dashboard-view-all-btn" href="{{ route('tickets.index') }}" data-auto-translate>{{ __('dashboard.view_all') }}</a>
            </div>
        </div>

        <table class="dashboard-latest-table dashboard-mobile-cards-table">
            <thead>
                <tr>
                    <th data-auto-translate>{{ __('common.ticket') }}</th>

                    @if ($role !== 'user')
                        <th data-auto-translate>{{ __('common.requester') }}</th>
                    @endif

                    <th data-auto-translate>{{ __('common.department') }}</th>
                    <th data-auto-translate>{{ __('common.status') }}</th>
                    <th data-auto-translate>{{ __('common.priority') }}</th>
                    <th data-auto-translate>{{ __('common.due_date') }}</th>
                    <th data-auto-translate>{{ __('common.updated') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestTickets as $ticket)
                    <tr>
                        <td data-label="{{ __('common.ticket') }}">
                            <a class="ticket-link" href="{{ route('tickets.show', $ticket) }}">
                                <strong>#{{ $ticket->ticket_number }}</strong>
                                <span>{{ $ticket->title }}</span>
                            </a>
                        </td>

                        @if ($role !== 'user')
                            <td data-label="{{ __('common.requester') }}">
                                @php
                                    $requester = $ticket->user;
                                    $requesterAvatarUrl = $avatarUrlFor($requester);
                                @endphp

                                <div class="person">
                                    <div class="mini-avatar {{ $requesterAvatarUrl ? 'has-image' : '' }}">
                                        @if ($requesterAvatarUrl)
                                            <img
                                                src="{{ $requesterAvatarUrl }}"
                                                alt="{{ $requester?->name ?? __('common.requester') }} avatar"
                                                class="mini-avatar-img"
                                            >
                                        @else
                                            <span class="avatar-fallback">?</span>
                                        @endif
                                    </div>

                                    <div>
                                        <strong>{{ $requester?->name ?? __('common.unknown') }}</strong><br>
                                        <small data-auto-translate>{{ __('common.requester') }}</small>
                                    </div>
                                </div>
                            </td>
                        @endif

                        <td data-label="{{ __('common.department') }}">{{ $ticket->department?->name ?? __('common.no_department') }}</td>
                        <td data-label="{{ __('common.status') }}"><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                        <td data-label="{{ __('common.priority') }}">
                            <span class="priority {{ $ticket->priority ?? 'unset' }}">
                                {{ $ticket->priority ? ucfirst($ticket->priority) : __('common.not_set') }}
                            </span>
                        </td>

                        @php
                            $isOverdue = $ticket->due_at
                                && $ticket->due_at->isPast()
                                && ! in_array($ticket->status, ['solved', 'closed'], true);
                        @endphp

                        <td data-label="{{ __('common.due_date') }}">
                            @if ($ticket->due_at)
                                <div class="due-date-info {{ $isOverdue ? 'overdue' : '' }}">
                                    <strong>{{ $ticket->due_at->format('M d, Y') }}</strong>
                                    <small>{{ $isOverdue ? __('common.overdue') : $ticket->due_at->diffForHumans() }}</small>
                                </div>
                            @else
                                <span class="page-subtitle">{{ __('common.not_set') }}</span>
                            @endif
                        </td>

                        <td data-label="{{ __('common.updated') }}">{{ $ticket->updated_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $role === 'user' ? 6 : 7 }}">
                            <div class="empty" data-auto-translate>{{ __('dashboard.no_tickets') }}</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section id="recent-activity" class="card table-card dashboard-activity-card">
        <div class="table-head activity-head">
            <div>
                <h2 data-auto-translate>{{ __('dashboard.recent_activity') }}</h2>
                <p class="page-subtitle">{{ $activitySubtitle }}</p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}#recent-activity" class="activity-search-form" id="activitySearchForm">
                <input
                    type="search"
                    name="activity_search"
                    id="activitySearchInput"
                    value="{{ $activitySearch ?? request('activity_search') }}"
                    placeholder="{{ __('dashboard.search_logs') }}"
                    autocomplete="off"
                    data-auto-translate-attribute="placeholder"
                >

                <button type="submit" class="btn btn-sm btn-secondary activity-search-btn" id="activitySearchBtn" data-auto-translate>
                    {{ __('dashboard.search') }}
                </button>

                @if ($activitySearch !== '')
                    <a href="{{ route('dashboard') }}#recent-activity" class="btn btn-sm btn-secondary activity-search-reset">
                        {{ __('common.reset') }}
                    </a>
                @endif

                <div
                    class="activity-search-status"
                    id="activitySearchStatus"
                    {{ $activitySearch !== '' ? '' : 'hidden' }}
                >
                    @if ($activitySearch !== '')
                        {{ __('common.search_applied') }} {{ method_exists($latestActivities, 'total') ? $latestActivities->total() : $latestActivities->count() }} {{ __('common.activity_logs_found') }}
                    @endif
                </div>
            </form>
        </div>

        <div class="activity-list">
            @forelse($latestActivities as $activity)
                <div
                    class="activity-item activity-openable"
                    role="button"
                    tabindex="0"
                    data-action="{{ e($activity->action) }}"
                    data-ticket="{{ e($activity->ticket?->ticket_number ?? __('common.ticket_removed')) }}"
                    data-title="{{ e($activity->ticket?->title ?? __('common.deleted_or_unavailable')) }}"
                    data-user="{{ e($activity->user?->name ?? __('common.system')) }}"
                    data-old="{{ e($activity->old_value ?? __('common.not_set')) }}"
                    data-new="{{ e($activity->new_value ?? __('common.not_set')) }}"
                    data-time="{{ e($activity->created_at?->format('M d, Y - h:i A') ?? '') }}"
                    data-url="{{ $activity->ticket ? route('tickets.show', $activity->ticket) : '' }}"
                >
                    <div class="activity-dot"></div>

                    <div class="activity-content">
                        <strong>{{ $activity->action }}</strong>

                        <span>
                            @if($activity->ticket)
                                #{{ $activity->ticket->ticket_number }}
                            @else
                                <span data-auto-translate>{{ __('common.ticket_removed') }}</span>
                            @endif
                        </span>

                        <span>
                            @if($activity->user)
                                <span data-auto-translate>{{ __('common.by') }}</span> {{ $activity->user->name }}
                            @endif
                        </span>

                        @if($activity->old_value || $activity->new_value)
                            <small>
                                @if($activity->old_value)
                                    <span data-auto-translate>{{ __('common.from') }}:</span> {{ \Illuminate\Support\Str::limit($activity->old_value, 90) }}
                                @endif

                                @if($activity->old_value && $activity->new_value)
                                    →
                                @endif

                                @if($activity->new_value)
                                    <span data-auto-translate>{{ __('common.to') }}:</span> {{ \Illuminate\Support\Str::limit($activity->new_value, 90) }}
                                @endif
                            </small>
                        @endif
                    </div>

                    <button type="button" class="activity-view-btn" data-auto-translate>
                        {{ __('common.view_details') }}
                    </button>

                    <small>{{ $activity->created_at?->diffForHumans() }}</small>
                </div>
            @empty
                <div class="empty" data-auto-translate>
                    {{ $activitySearch !== '' ? __('common.no_activity_logs_found') : __('common.no_recent_activity') }}
                </div>
            @endforelse
        </div>

        @if (method_exists($latestActivities, 'hasPages') && $latestActivities->hasPages())
            @php
                if (method_exists($latestActivities, 'fragment')) {
                    $latestActivities->fragment('recent-activity');
                }
            @endphp

            <div class="pagination-wrap">
                {{ $latestActivities->links('vendor.pagination.resolveiq') }}
            </div>
        @endif
    </section>

    <div class="activity-modal-backdrop" id="activityModalBackdrop" hidden>
        <div class="activity-modal" role="dialog" aria-modal="true" aria-labelledby="activityModalTitle">
            <div class="activity-modal-head">
                <div>
                    <span class="activity-modal-kicker" data-auto-translate>{{ __('common.activity_details') }}</span>
                    <h3 id="activityModalTitle" data-auto-translate>{{ __('common.activity') }}</h3>
                </div>

                <button type="button" class="activity-modal-close" id="activityModalClose" aria-label="{{ __('common.close') }}">
                    <x-icon name="close" />
                </button>
            </div>

            <div class="activity-modal-body">
                <div class="activity-detail-grid">
                    <div>
                        <small data-auto-translate>{{ __('common.ticket') }}</small>
                        <strong id="activityModalTicket">-</strong>
                        <span id="activityModalTicketTitle">-</span>
                    </div>

                    <div>
                        <small data-auto-translate>{{ __('common.changed_by') }}</small>
                        <strong id="activityModalUser">-</strong>
                    </div>

                    <div>
                        <small data-auto-translate>{{ __('common.time') }}</small>
                        <strong id="activityModalTime">-</strong>
                    </div>
                </div>

                <div class="activity-change-box">
                    <small data-auto-translate>{{ __('common.previous_value') }}</small>
                    <pre id="activityModalOld">-</pre>
                </div>

                <div class="activity-change-box">
                    <small data-auto-translate>{{ __('common.new_value') }}</small>
                    <pre id="activityModalNew">-</pre>
                </div>
            </div>

            <div class="activity-modal-actions">
                <a href="#" class="btn btn-primary" id="activityModalTicketLink" data-auto-translate>
                    {{ __('common.open_ticket') }}
                </a>

                <button type="button" class="btn btn-secondary" id="activityModalCancel" data-auto-translate>
                    {{ __('common.close') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const activityItems = document.querySelectorAll('.activity-openable');
            const modalBackdrop = document.getElementById('activityModalBackdrop');
            if (modalBackdrop && modalBackdrop.parentElement !== document.body) {
                document.body.appendChild(modalBackdrop);
            }
            const modalClose = document.getElementById('activityModalClose');
            const modalCancel = document.getElementById('activityModalCancel');
            const ticketLink = document.getElementById('activityModalTicketLink');

            const title = document.getElementById('activityModalTitle');
            const ticket = document.getElementById('activityModalTicket');
            const ticketTitle = document.getElementById('activityModalTicketTitle');
            const user = document.getElementById('activityModalUser');
            const time = document.getElementById('activityModalTime');
            const oldValue = document.getElementById('activityModalOld');
            const newValue = document.getElementById('activityModalNew');

            function openActivityModal(item) {
                if (!item) {
                    return;
                }

                title.textContent = item.dataset.action || @json(__('common.activity'));
                ticket.textContent = item.dataset.ticket || '-';
                ticketTitle.textContent = item.dataset.title || '-';
                user.textContent = item.dataset.user || @json(__('common.system'));
                time.textContent = item.dataset.time || '-';
                oldValue.textContent = item.dataset.old || @json(__('common.not_set'));
                newValue.textContent = item.dataset.new || @json(__('common.not_set'));

                if (item.dataset.url) {
                    ticketLink.href = item.dataset.url;
                    ticketLink.style.display = 'inline-flex';
                } else {
                    ticketLink.href = '#';
                    ticketLink.style.display = 'none';
                }

                modalBackdrop.hidden = false;
                document.body.classList.add('modal-open');
            }

            function closeActivityModal() {
                modalBackdrop.hidden = true;
                document.body.classList.remove('modal-open');
            }

            document.querySelectorAll('.activity-openable').forEach(item => {
                item.addEventListener('click', event => {
                    if (event.target.closest('a, button')) {
                        return;
                    }

                    openActivityModal(item);
                });

                item.addEventListener('keydown', event => {
                    if (event.key === 'Enter') {
                        openActivityModal(item);
                    }
                });
            });

            document.querySelectorAll('.activity-view-btn').forEach(button => {
                button.addEventListener('click', event => {
                    event.stopPropagation();
                    openActivityModal(button.closest('.activity-openable'));
                });
            });

            modalClose?.addEventListener('click', closeActivityModal);
            modalCancel?.addEventListener('click', closeActivityModal);

            modalBackdrop?.addEventListener('click', event => {
                if (event.target === modalBackdrop) {
                    closeActivityModal();
                }
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && modalBackdrop && !modalBackdrop.hidden) {
                    closeActivityModal();
                }
            });
        })();
    </script>
@endsection
