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

    <section class="card table-card">
        <div class="table-head">
            <div>
                <h2>{{ $ticketsTitle }}</h2>
                <p class="page-subtitle">{{ $ticketsSubtitle }}</p>
            </div>

            <a class="btn" href="{{ route('tickets.index') }}">View All</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Ticket</th>

                    @if ($role !== 'user')
                        <th>Requester</th>
                    @endif

                    <th>Department</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestTickets as $ticket)
                    <tr>
                        <td>
                            <a class="ticket-link" href="{{ route('tickets.show', $ticket) }}">
                                <strong>#{{ $ticket->ticket_number }}</strong>
                                <span>{{ $ticket->title }}</span>
                            </a>
                        </td>

                        @if ($role !== 'user')
                            <td>
                                <div class="person">
                                    <div class="mini-avatar">
                                        {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $ticket->user?->name ?? 'Unknown' }}</strong><br>
                                        <small>Requester</small>
                                    </div>
                                </div>
                            </td>
                        @endif

                        <td>{{ $ticket->department?->name ?? 'No department' }}</td>
                        <td><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                        <td><span class="priority {{ $ticket->priority ?? 'unset' }}">
                            {{ $ticket->priority ? ucfirst($ticket->priority) : 'Not set' }}
                        </span></td>
                        <td>{{ $ticket->updated_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $role === 'user' ? 5 : 6 }}">
                            <div class="empty">No tickets found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="card table-card dashboard-activity-card">
        <div class="table-head">
            <div>
                <h2>Recent Activity</h2>
                <p class="page-subtitle">{{ $activitySubtitle }}</p>
            </div>
        </div>

        <div class="activity-list">
            @forelse($latestActivities as $activity)
                <div class="activity-item">
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
                                    From: {{ $activity->old_value }}
                                @endif

                                @if($activity->old_value && $activity->new_value)
                                    →
                                @endif

                                @if($activity->new_value)
                                    To: {{ $activity->new_value }}
                                @endif
                            </small>
                        @endif
                    </div>

                    <small>{{ $activity->created_at?->diffForHumans() }}</small>
                </div>
            @empty
                <div class="empty">No recent activity yet.</div>
            @endforelse
        </div>

        @if (method_exists($latestActivities, 'hasPages') && $latestActivities->hasPages())
            <div class="pagination-wrap">
                {{ $latestActivities->links('vendor.pagination.resolveiq') }}
            </div>
        @endif
    </section>
@endsection
