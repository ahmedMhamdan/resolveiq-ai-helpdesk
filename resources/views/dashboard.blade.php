@extends('layouts.app')

@section('title', 'Dashboard')

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

        $dashboardTitle = $role === 'agent' ? 'Agent Dashboard' : ($role === 'user' ? 'My Dashboard' : 'Dashboard');
        $dashboardSubtitle = $role === 'agent'
            ? 'Overview of your assigned tickets, pending work, and urgent requests.'
            : ($role === 'user'
                ? 'Track your support tickets and recent request updates.'
                : 'Overview of support performance, ticket volume, and urgent issues.');

        $ticketsTitle = $role === 'agent' ? 'Assigned Tickets' : ($role === 'user' ? 'My Tickets' : 'Latest Tickets');
        $ticketsSubtitle = $role === 'agent'
            ? 'Latest tickets assigned to you.'
            : ($role === 'user'
                ? 'Latest support requests created by you.'
                : 'Newest support requests in the workspace.');

        $activitySubtitle = $role === 'admin'
            ? 'Latest ticket updates and workspace actions.'
            : 'Latest updates related to your tickets.';

        $avatarUrlFor = function ($person) {
            if (! $person || ! $person->avatar_path) {
                return '';
            }

            if (method_exists($person, 'avatarUrl')) {
                return $person->avatarUrl();
            }

            return str_starts_with($person->avatar_path, 'images/')
                ? asset($person->avatar_path)
                : asset('storage/' . $person->avatar_path);
        };

        $initialsFor = function ($name) {
            $parts = preg_split('/\s+/', trim($name ?: 'U'));

            if (count($parts) >= 2) {
                return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
            }

            return strtoupper(substr($parts[0] ?? 'U', 0, 1));
        };
    @endphp

    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $dashboardTitle }}</h1>
            <p class="page-subtitle">{{ $dashboardSubtitle }}</p>
        </div>

        <a class="btn secondary" href="{{ route('tickets.index') }}">View Tickets</a>
    </div>

    <section class="grid stats">
        <div class="card stat-card">
            <div class="stat-top">
                <span>Open Tickets</span>
                <span class="stat-icon">O</span>
            </div>
            <div class="stat-number">{{ $stats['open'] ?? 0 }}</div>
            <div class="stat-trend">Active requests</div>
        </div>

        <div class="card stat-card">
            <div class="stat-top">
                <span>Pending</span>
                <span class="stat-icon">P</span>
            </div>
            <div class="stat-number">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-trend">Waiting for updates</div>
        </div>

        <div class="card stat-card">
            <div class="stat-top">
                <span>Solved</span>
                <span class="stat-icon">S</span>
            </div>
            <div class="stat-number">{{ $stats['solved'] ?? 0 }}</div>
            <div class="stat-trend">Resolved tickets</div>
        </div>

        <div class="card stat-card">
            <div class="stat-top">
                <span>Urgent</span>
                <span class="stat-icon">U</span>
            </div>
            <div class="stat-number">{{ $stats['urgent'] ?? 0 }}</div>
            <div class="stat-trend">Needs attention</div>
        </div>
    </section>

    <section class="card table-card dashboard-tickets-card">
        <div class="table-head">
            <div>
                <h2>{{ $ticketsTitle }}</h2>
                <p class="page-subtitle">{{ $ticketsSubtitle }}</p>
            </div>

            <a class="btn" href="{{ route('tickets.index') }}">View All</a>
        </div>

        <table class="dashboard-latest-table">
            <thead>
                <tr>
                    <th>Ticket</th>

                    @if ($role !== 'user')
                        <th>Requester</th>
                    @endif

                    <th>Department</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Due Date</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestTickets as $ticket)
                    <tr>
                        <td data-label="Ticket">
                            <a class="ticket-link" href="{{ route('tickets.show', $ticket) }}">
                                <strong>#{{ $ticket->ticket_number }}</strong>
                                <span>{{ $ticket->title }}</span>
                            </a>
                        </td>

                        @if ($role !== 'user')
                            <td data-label="Requester">
                                @php
                                    $requester = $ticket->user;
                                    $requesterAvatarUrl = $avatarUrlFor($requester);
                                @endphp

                                <div class="person">
                                    <div class="mini-avatar {{ $requesterAvatarUrl ? 'has-image' : '' }}">
                                        @if ($requesterAvatarUrl)
                                            <img
                                                src="{{ $requesterAvatarUrl }}"
                                                alt="{{ $requester?->name ?? 'Requester' }} avatar"
                                                class="mini-avatar-img"
                                            >
                                        @else
                                            {{ $initialsFor($requester?->name ?? 'Unknown') }}
                                        @endif
                                    </div>

                                    <div>
                                        <strong>{{ $requester?->name ?? 'Unknown' }}</strong><br>
                                        <small>Requester</small>
                                    </div>
                                </div>
                            </td>
                        @endif

                        <td data-label="Department">{{ $ticket->department?->name ?? 'No department' }}</td>
                        <td data-label="Status"><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                        <td data-label="Priority">
                            <span class="priority {{ $ticket->priority ?? 'unset' }}">
                                {{ $ticket->priority ? ucfirst($ticket->priority) : 'Not set' }}
                            </span>
                        </td>

                        @php
                            $isOverdue = $ticket->due_at
                                && $ticket->due_at->isPast()
                                && ! in_array($ticket->status, ['solved', 'closed'], true);
                        @endphp

                        <td data-label="Due Date">
                            @if ($ticket->due_at)
                                <div class="due-date-info {{ $isOverdue ? 'overdue' : '' }}">
                                    <strong>{{ $ticket->due_at->format('M d, Y') }}</strong>
                                    <small>{{ $isOverdue ? 'Overdue' : $ticket->due_at->diffForHumans() }}</small>
                                </div>
                            @else
                                <span class="page-subtitle">Not set</span>
                            @endif
                        </td>

                        <td data-label="Updated">{{ $ticket->updated_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $role === 'user' ? 6 : 7 }}">
                            <div class="empty">No tickets found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section id="recent-activity" class="card table-card dashboard-activity-card">
        <div class="table-head activity-head">
            <div>
                <h2>Recent Activity</h2>
                <p class="page-subtitle">{{ $activitySubtitle }}</p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}#recent-activity" class="activity-search-form">
                <input
                    type="text"
                    name="activity_search"
                    value="{{ $activitySearch ?? request('activity_search') }}"
                    placeholder="Search logs..."
                >

                <button type="submit" class="btn btn-sm btn-primary">
                    Search
                </button>

                @if(request('activity_search'))
                    <a href="{{ route('dashboard') }}#recent-activity" class="btn btn-sm btn-secondary">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="activity-list">
            @forelse($latestActivities as $activity)
                <div
                    class="activity-item activity-openable"
                    role="button"
                    tabindex="0"
                    data-action="{{ e($activity->action) }}"
                    data-ticket="{{ e($activity->ticket?->ticket_number ?? 'Ticket removed') }}"
                    data-title="{{ e($activity->ticket?->title ?? 'Deleted or unavailable ticket') }}"
                    data-user="{{ e($activity->user?->name ?? 'System') }}"
                    data-old="{{ e($activity->old_value ?? 'Not set') }}"
                    data-new="{{ e($activity->new_value ?? 'Not set') }}"
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
                                Ticket removed
                            @endif

                            @if($activity->user)
                                by {{ $activity->user->name }}
                            @endif
                        </span>

                        @if($activity->old_value || $activity->new_value)
                            <small>
                                @if($activity->old_value)
                                    From: {{ \Illuminate\Support\Str::limit($activity->old_value, 90) }}
                                @endif

                                @if($activity->old_value && $activity->new_value)
                                    →
                                @endif

                                @if($activity->new_value)
                                    To: {{ \Illuminate\Support\Str::limit($activity->new_value, 90) }}
                                @endif
                            </small>
                        @endif
                    </div>

                    <button type="button" class="activity-view-btn">
                        View details
                    </button>

                    <small>{{ $activity->created_at?->diffForHumans() }}</small>
                </div>
            @empty
                <div class="empty">
                    {{ request('activity_search') ? 'No activity logs matched your search.' : 'No recent activity yet.' }}
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
                    <span class="activity-modal-kicker">Activity Details</span>
                    <h3 id="activityModalTitle">Activity</h3>
                </div>

                <button type="button" class="activity-modal-close" id="activityModalClose">
                    ×
                </button>
            </div>

            <div class="activity-modal-body">
                <div class="activity-detail-grid">
                    <div>
                        <small>Ticket</small>
                        <strong id="activityModalTicket">-</strong>
                        <span id="activityModalTicketTitle">-</span>
                    </div>

                    <div>
                        <small>Changed by</small>
                        <strong id="activityModalUser">-</strong>
                    </div>

                    <div>
                        <small>Time</small>
                        <strong id="activityModalTime">-</strong>
                    </div>
                </div>

                <div class="activity-change-box">
                    <small>Previous value</small>
                    <pre id="activityModalOld">-</pre>
                </div>

                <div class="activity-change-box">
                    <small>New value</small>
                    <pre id="activityModalNew">-</pre>
                </div>
            </div>

            <div class="activity-modal-actions">
                <a href="#" class="btn btn-primary" id="activityModalTicketLink">
                    Open Ticket
                </a>

                <button type="button" class="btn btn-secondary" id="activityModalCancel">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        (() => {
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

                title.textContent = item.dataset.action || 'Activity';
                ticket.textContent = item.dataset.ticket || '-';
                ticketTitle.textContent = item.dataset.title || '-';
                user.textContent = item.dataset.user || 'System';
                time.textContent = item.dataset.time || '-';
                oldValue.textContent = item.dataset.old || 'Not set';
                newValue.textContent = item.dataset.new || 'Not set';

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
