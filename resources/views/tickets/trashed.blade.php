@extends('layouts.app')

@section('title', 'Deleted Tickets')

@section('content')
<div class="page-head">
    <div>
        <h1>Deleted Tickets</h1>
        <p>Restore deleted tickets or permanently remove them.</p>
    </div>

    <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-page-back">
        Back to Tickets
    </a>
</div>

<div class="table-card deleted-tickets-card" id="deleted-ticket-list">
    <div class="card-head">
        <div>
            <h2>Deleted Tickets</h2>
            <p>Tickets moved to archive by soft delete.</p>
        </div>

        <form
            method="GET"
            action="{{ route('tickets.trashed') }}#deleted-ticket-list"
            class="filters deleted-ticket-search"
            data-live-table=".deleted-tickets-table"
            data-live-empty="#deleted-empty-message"
        >
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search deleted ticket..."
                autocomplete="off"
                class="deleted-ticket-search-input"
            >

            <button type="submit" class="deleted-ticket-search-btn">Search</button>

            <button type="button" class="btn btn-secondary deleted-ticket-reset" hidden>
                Reset
            </button>

            <p id="deleted-search-status" class="deleted-search-status" aria-live="polite" hidden></p>
        </form>
    </div>

    <div class="table-wrap deleted-tickets-wrap">
        <table class="deleted-tickets-table">
            <thead>
                <tr>
                    <th class="col-ticket">Ticket</th>
                    <th class="col-person">Requester</th>
                    <th class="col-person">Agent</th>
                    <th class="col-department">Department</th>
                    <th class="col-status">Status</th>
                    <th class="col-priority">Priority</th>
                    <th class="col-date">Due Date</th>
                    <th class="col-date">Deleted At</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($tickets as $ticket)
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

                        $agent = $ticket->agent;
                        $agentAvatarUrl = null;

                        if ($agent?->avatar_path) {
                            $agentAvatarUrl = method_exists($agent, 'avatarUrl')
                                ? $agent->avatarUrl()
                                : (str_starts_with($agent->avatar_path, 'images/')
                                    ? asset($agent->avatar_path)
                                    : asset('storage/' . $agent->avatar_path));
                        }

                        $isOverdue = $ticket->due_at
                            && $ticket->due_at->isPast()
                            && ! in_array($ticket->status, ['solved', 'closed'], true);
                    @endphp

                    <tr class="deleted-ticket-row">
                        <td class="ticket-cell" data-label="Ticket">
                            <strong class="ticket-number">#{{ $ticket->ticket_number }}</strong>
                            <div class="ticket-title">{{ $ticket->title }}</div>
                        </td>

                        <td data-label="Requester">
                            <div class="person ticket-person">
                                <div class="mini-avatar">
                                    @if ($requesterAvatarUrl)
                                        <img src="{{ $requesterAvatarUrl }}" alt="{{ $requester?->name ?? 'Requester' }} avatar">
                                    @else
                                        {{ strtoupper(substr($requester?->name ?? 'U', 0, 1)) }}
                                    @endif
                                </div>

                                <div class="person-meta">
                                    <strong>{{ $requester?->name ?? 'Unknown' }}</strong>
                                    <small>Requester</small>
                                </div>
                            </div>
                        </td>

                        <td data-label="Agent">
                            @if ($agent)
                                <div class="person ticket-person">
                                    <div class="mini-avatar">
                                        @if ($agentAvatarUrl)
                                            <img src="{{ $agentAvatarUrl }}" alt="{{ $agent->name }} avatar">
                                        @else
                                            {{ strtoupper(substr($agent->name, 0, 1)) }}
                                        @endif
                                    </div>

                                    <div class="person-meta">
                                        <strong>{{ $agent->name }}</strong>
                                        <small>Agent</small>
                                    </div>
                                </div>
                            @else
                                <span class="page-subtitle">Unassigned</span>
                            @endif
                        </td>

                        <td class="department-cell" data-label="Department">
                            {{ $ticket->department?->name ?? 'No department' }}
                        </td>

                        <td data-label="Status">
                            <span class="badge {{ $ticket->status }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>

                        <td data-label="Priority">
                            <span class="priority {{ $ticket->priority ?? 'unset' }}">
                                {{ $ticket->priority ? ucfirst($ticket->priority) : 'Not set' }}
                            </span>
                        </td>

                        <td class="date-cell" data-label="Due Date">
                            @if ($ticket->due_at)
                                <div class="due-date-info {{ $isOverdue ? 'overdue' : '' }}">
                                    <strong>{{ $ticket->due_at->format('M d, Y') }}</strong>
                                    <small>{{ $isOverdue ? 'Overdue' : $ticket->due_at->diffForHumans() }}</small>
                                </div>
                            @else
                                <span class="page-subtitle">Not set</span>
                            @endif
                        </td>

                        <td class="date-cell" data-label="Deleted At">
                            {{ $ticket->deleted_at?->diffForHumans() }}
                        </td>

                        <td data-label="Actions">
                            <div class="deleted-ticket-actions">
                                <form action="{{ route('tickets.restore', $ticket->id) }}" method="POST">
                                    @csrf

                                    <button type="submit" class="btn btn-sm btn-restore-soft">
                                        Restore
                                    </button>
                                </form>

                                <form action="{{ route('tickets.forceDelete', $ticket->id) }}" method="POST" onsubmit="return confirm('This will permanently delete the ticket. Continue?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger-soft btn-sm">
                                        Delete Forever
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            No deleted tickets found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p id="deleted-empty-message" class="live-search-empty" hidden>
            No matching deleted tickets found.
        </p>
    </div>

    <div class="pagination-wrap">
        {{ $tickets->links('vendor.pagination.resolveiq') }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.deleted-ticket-search');

        if (!form) {
            return;
        }

        const input = form.querySelector('.deleted-ticket-search-input');
        const submitButton = form.querySelector('.deleted-ticket-search-btn');
        const reset = form.querySelector('.deleted-ticket-reset');
        const table = document.querySelector(form.dataset.liveTable);
        const empty = document.querySelector(form.dataset.liveEmpty);
        const status = document.querySelector('#deleted-search-status');
        const card = document.querySelector('#deleted-ticket-list');

        if (!input || !table) {
            return;
        }

        const rows = Array.from(table.querySelectorAll('tbody tr.deleted-ticket-row'));

        function getRowText(row) {
            const clone = row.cloneNode(true);

            clone.querySelectorAll('button, form').forEach(function (element) {
                element.remove();
            });

            return clone.textContent.replace(/\s+/g, ' ').trim().toLowerCase();
        }

        rows.forEach(function (row) {
            row.dataset.searchText = getRowText(row);
        });

        function showStatus(message, type) {
            if (!status) {
                return;
            }

            status.textContent = message;
            status.hidden = false;
            status.classList.remove('is-success', 'is-warning', 'is-info', 'is-visible');
            status.classList.add('is-' + type);

            void status.offsetWidth;
            status.classList.add('is-visible');
        }

        function pulseResults() {
            if (!card) {
                return;
            }

            card.classList.remove('search-pulse');
            void card.offsetWidth;
            card.classList.add('search-pulse');

            window.setTimeout(function () {
                card.classList.remove('search-pulse');
            }, 520);
        }

        function buildStatusMessage(term, visibleCount) {
            const cleanTerm = input.value.replace(/\s+/g, ' ').trim();

            if (!term) {
                return 'Search cleared. Showing all deleted tickets.';
            }

            if (visibleCount === 0) {
                return 'No results found for "' + cleanTerm + '".';
            }

            if (visibleCount === 1) {
                return 'Search applied. 1 deleted ticket found for "' + cleanTerm + '".';
            }

            return 'Search applied. ' + visibleCount + ' deleted tickets found for "' + cleanTerm + '".';
        }

        function runSearch(showFeedback) {
            const term = input.value.replace(/\s+/g, ' ').trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(function (row) {
                const shouldShow = !term || row.dataset.searchText.includes(term);

                row.classList.toggle('is-hidden', !shouldShow);
                row.classList.toggle('is-search-match', Boolean(term && shouldShow));

                if (shouldShow) {
                    visibleCount++;
                }
            });

            if (reset) {
                reset.hidden = !term;
            }

            if (empty) {
                empty.hidden = rows.length === 0 || visibleCount > 0;
            }

            if (showFeedback) {
                const statusType = !term ? 'info' : (visibleCount > 0 ? 'success' : 'warning');

                showStatus(buildStatusMessage(term, visibleCount), statusType);
                pulseResults();
            }
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            form.classList.add('is-searching');

            if (submitButton) {
                submitButton.classList.add('is-loading');
                submitButton.disabled = true;
            }

            window.setTimeout(function () {
                runSearch(true);
                input.blur();

                form.classList.remove('is-searching');

                if (submitButton) {
                    submitButton.classList.remove('is-loading');
                    submitButton.disabled = false;
                }
            }, 180);
        });

        if (reset) {
            reset.addEventListener('click', function () {
                input.value = '';
                runSearch(true);
                input.focus();
            });
        }

        if (input.value.trim()) {
            runSearch(true);
        }
    });
</script>

@endsection
