@extends('layouts.app')

@section('title', 'Overdue Tickets')

@section('content')
    <div class="page-head">
        <div>
            <h1>Overdue Tickets</h1>
            <p class="page-subtitle">
                Review tickets that passed their due date and still need action.
            </p>
        </div>
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-page-back">
            Back to Tickets
        </a>
    </div>

    <div class="table-card overdue-table-card" id="overdue-ticket-list">
        <div class="table-head">
            <h2>Needs Attention</h2>

            <form
                method="GET"
                action="{{ route('tickets.overdue') }}#overdue-ticket-list"
                class="filters js-live-ticket-search"
                data-live-table=".overdue-table"
                data-live-empty="#overdue-empty-message"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search overdue ticket..."
                    autocomplete="off"
                    class="js-live-ticket-input"
                >

                <button type="submit">Search</button>

                <button type="button" class="btn btn-secondary js-live-ticket-reset" hidden>
                    Reset
                </button>
            </form>
        </div>

        <div class="table-wrap">
            <table class="overdue-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th class="users-center-col">Requester</th>
                        <th class="users-center-col">Agent</th>
                        <th class="users-center-col">Department</th>
                        <th class="users-center-col">Due Date</th>
                        <th class="users-center-col">Status</th>
                        <th class="users-center-col">Priority</th>
                        <th class="users-center-col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr class="live-ticket-row">
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="ticket-link">
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                    <span>{{ $ticket->title }}</span>
                                </a>
                            </td>

                            <td>
                                @php
                                    $requester = $ticket->user;
                                    $requesterAvatarUrl = null;

                                    if ($requester?->avatar_path) {
                                        $requesterAvatarUrl = method_exists($requester, 'avatarUrl')
                                            ? $requester->avatarUrl()
                                            : (str_starts_with($requester->avatar_path, 'images/')
                                                ? asset($requester->avatar_path)
                                                : asset('storage/' . $requester->avatar_path));
                                    }
                                @endphp

                                <div class="person">
                                    <span class="mini-avatar">
                                        @if ($requesterAvatarUrl)
                                            <img src="{{ $requesterAvatarUrl }}" alt="{{ $requester?->name ?? 'Requester' }} avatar">
                                        @else
                                            <span class="avatar-fallback">?</span>
                                        @endif
                                    </span>

                                    <div>
                                        <strong>{{ $requester?->name ?? 'Unknown' }}</strong>
                                        <br>
                                        <small>{{ $requester?->email ?? 'No email' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="users-center-col">
                                @if ($ticket->agent)
                                    @php
                                        $agent = $ticket->agent;
                                        $agentAvatarUrl = null;

                                        if ($agent?->avatar_path) {
                                            $agentAvatarUrl = method_exists($agent, 'avatarUrl')
                                                ? $agent->avatarUrl()
                                                : (str_starts_with($agent->avatar_path, 'images/')
                                                    ? asset($agent->avatar_path)
                                                    : asset('storage/' . $agent->avatar_path));
                                        }
                                    @endphp

                                    <div class="person overdue-agent-person">
                                        <span class="mini-avatar">
                                            @if ($agentAvatarUrl)
                                                <img src="{{ $agentAvatarUrl }}" alt="{{ $agent->name }} avatar">
                                            @else
                                                <span class="avatar-fallback">?</span>
                                            @endif
                                        </span>

                                        <div>
                                            <strong>{{ $agent->name }}</strong>
                                            <br>
                                            <small>{{ $agent->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="role-badge role-user">
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            <td class="users-center-col">
                                {{ $ticket->department?->name ?? 'No department' }}
                            </td>

                            <td class="users-center-col">
                                <span class="overdue-date-badge">
                                    {{ \Carbon\Carbon::parse($ticket->due_at)->format('M d, Y') }}
                                </span>
                            </td>

                            <td class="users-center-col">
                                <span class="badge {{ $ticket->status }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>

                            <td class="users-center-col">
                                @if ($ticket->priority)
                                    <span class="priority {{ $ticket->priority }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                @else
                                    <span class="priority unset">
                                        Not set
                                    </span>
                                @endif
                            </td>

                            <td class="users-center-col">
                                <div class="row-actions users-role-actions">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-view-ticket">
                                        View
                                    </a>

                                    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-sm">
                                        Manage
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">
                                No overdue tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <p id="overdue-empty-message" class="live-search-empty" hidden>
                No matching overdue tickets found.
            </p>
        </div>

        {{ $tickets->links('vendor.pagination.resolveiq') }}
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-live-ticket-search').forEach(function (form) {
            const input = form.querySelector('.js-live-ticket-input');
            const reset = form.querySelector('.js-live-ticket-reset');
            const table = document.querySelector(form.dataset.liveTable);
            const empty = document.querySelector(form.dataset.liveEmpty);

            if (!input || !table) {
                return;
            }

            const rows = Array.from(table.querySelectorAll('tbody tr.live-ticket-row'));

            function getCleanText(row) {
                const clone = row.cloneNode(true);

                clone.querySelectorAll('select, input, textarea, button, form').forEach(function (element) {
                    element.remove();
                });

                return clone.textContent.replace(/\s+/g, ' ').trim().toLowerCase();
            }

            function filterRows() {
                const term = input.value.replace(/\s+/g, ' ').trim().toLowerCase();
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const isVisible = !term || getCleanText(row).includes(term);
                    row.classList.toggle('is-hidden', !isVisible);

                    if (isVisible) {
                        visibleCount++;
                    }
                });

                if (reset) {
                    reset.hidden = !term;
                }

                if (empty) {
                    empty.hidden = visibleCount > 0;
                }
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                filterRows();
                input.focus();
            });

            input.addEventListener('input', filterRows);

            if (reset) {
                reset.addEventListener('click', function () {
                    input.value = '';
                    filterRows();
                    input.focus();
                });
            }

            filterRows();
        });
    });
</script>

@endsection
