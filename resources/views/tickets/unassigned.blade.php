@extends('layouts.app')

@section('title', 'Unassigned Tickets')

@section('content')
    <div class="page-head">
        <div>
            <h1>Unassigned Tickets</h1>
            <p class="page-subtitle">
                Review new tickets and assign them to support agents.
            </p>
        </div>

        <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-page-back">
            Back to Tickets
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" style="margin: 0 0 20px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-card unassigned-table-card" id="unassigned-ticket-list">
        <div class="table-head">
            <h2>Waiting for Assignment</h2>

            <form
                method="GET"
                action="{{ route('tickets.unassigned') }}#unassigned-ticket-list"
                class="filters unassigned-ticket-search"
                data-live-table=".unassigned-table"
                data-live-empty="#unassigned-empty-message"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search ticket..."
                    autocomplete="off"
                    class="unassigned-ticket-search-input"
                >

                <button type="submit" class="unassigned-ticket-search-btn">Search</button>

                <button type="button" class="btn btn-secondary unassigned-ticket-reset" hidden>
                    Reset
                </button>

                <p id="unassigned-search-status" class="unassigned-search-status" aria-live="polite" hidden></p>
            </form>
        </div>

        <div class="table-wrap">
            <table class="unassigned-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Requester</th>
                        <th class="users-center-col">Department</th>
                        <th class="users-center-col">Status</th>
                        <th class="users-center-col">Priority</th>
                        <th class="users-center-col">Due Date</th>
                        <th class="users-center-col">Assign Agent</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr class="unassigned-ticket-row">
                            <td data-label="Ticket">
                                <a href="{{ route('tickets.show', $ticket) }}" class="ticket-link">
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                    <span>{{ $ticket->title }}</span>
                                </a>
                            </td>

                            <td data-label="Requester">
                                <div class="person ticket-person">
                                    <span class="mini-avatar">
                                        @if ($ticket->user?->avatar_path)
                                            <img
                                                src="{{ method_exists($ticket->user, 'avatarUrl') ? $ticket->user->avatarUrl() : (str_starts_with($ticket->user->avatar_path, 'images/') ? asset($ticket->user->avatar_path) : asset('storage/' . $ticket->user->avatar_path)) }}"
                                                alt="{{ $ticket->user->name }} avatar"
                                            >
                                        @else
                                            {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                                        @endif
                                    </span>

                                    <div>
                                        <strong>{{ $ticket->user?->name ?? 'Unknown' }}</strong>
                                        <br>
                                        <small>{{ $ticket->user?->email ?? 'No email' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="users-center-col" data-label="Department">
                                {{ $ticket->department?->name ?? 'No department' }}
                            </td>

                            <td class="users-center-col" data-label="Status">
                                <span class="badge {{ $ticket->status }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>

                            <td class="users-center-col" data-label="Priority">
                                <select
                                    name="priority"
                                    form="assign-ticket-{{ $ticket->id }}"
                                    class="role-select priority-assign-select"
                                >
                                    <option value="" @selected($ticket->priority === null)>
                                        Not set
                                    </option>
                                    <option value="low" @selected($ticket->priority === 'low')>
                                        Low
                                    </option>
                                    <option value="medium" @selected($ticket->priority === 'medium')>
                                        Medium
                                    </option>
                                    <option value="high" @selected($ticket->priority === 'high')>
                                        High
                                    </option>
                                    <option value="urgent" @selected($ticket->priority === 'urgent')>
                                        Urgent
                                    </option>
                                </select>
                            </td>

                            <td class="users-center-col" data-label="Due Date">
                                <input
                                    type="date"
                                    name="due_at"
                                    form="assign-ticket-{{ $ticket->id }}"
                                    value="{{ $ticket->due_at ? $ticket->due_at->format('Y-m-d') : '' }}"
                                    class="unassigned-due-date-input"
                                >
                            </td>

                            <td class="users-center-col" data-label="Assign Agent">
                                <form
                                    id="assign-ticket-{{ $ticket->id }}"
                                    method="POST"
                                    action="{{ route('tickets.assignAgent', $ticket) }}"
                                    class="row-actions users-role-actions"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <select name="agent_id" class="role-select" required>
                                        <option value="">Select agent</option>

                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->id }}">
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="btn btn-sm btn-assign-agent">
                                        Assign
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">
                                No unassigned tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <p id="unassigned-empty-message" class="live-search-empty" hidden>
                No matching unassigned tickets found.
            </p>
        </div>

        {{ $tickets->links('vendor.pagination.resolveiq') }}
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.unassigned-ticket-search');

        if (!form) {
            return;
        }

        const input = form.querySelector('.unassigned-ticket-search-input');
        const submitButton = form.querySelector('.unassigned-ticket-search-btn');
        const reset = form.querySelector('.unassigned-ticket-reset');
        const table = document.querySelector(form.dataset.liveTable);
        const empty = document.querySelector(form.dataset.liveEmpty);
        const status = document.querySelector('#unassigned-search-status');
        const card = document.querySelector('#unassigned-ticket-list');

        if (!input || !table) {
            return;
        }

        const rows = Array.from(table.querySelectorAll('tbody tr.unassigned-ticket-row'));

        function getRowText(row) {
            const clone = row.cloneNode(true);

            clone.querySelectorAll('select, input, textarea, button, form').forEach(function (element) {
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

        function hideStatus() {
            if (!status) {
                return;
            }

            status.hidden = true;
            status.textContent = '';
            status.classList.remove('is-success', 'is-warning', 'is-info', 'is-visible');
        }

        function runSearch(showMessage = true) {
            const term = input.value.replace(/\s+/g, ' ').trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(function (row) {
                const shouldShow = !term || row.dataset.searchText.includes(term);

                row.classList.toggle('is-hidden', !shouldShow);
                row.classList.remove('is-search-match');

                if (shouldShow) {
                    visibleCount++;

                    if (term) {
                        void row.offsetWidth;
                        row.classList.add('is-search-match');
                    }
                }
            });

            if (reset) {
                reset.hidden = !term;
            }

            if (empty) {
                empty.hidden = rows.length === 0 || visibleCount > 0;
            }

            if (card) {
                card.classList.remove('search-pulse');
                void card.offsetWidth;
                card.classList.add('search-pulse');
            }

            if (!showMessage) {
                return;
            }

            if (!term) {
                showStatus('Search cleared. Showing all unassigned tickets.', 'info');
                return;
            }

            if (visibleCount === 0) {
                showStatus('No results found for "' + input.value.trim() + '".', 'warning');
                return;
            }

            const label = visibleCount === 1 ? 'unassigned ticket' : 'unassigned tickets';
            showStatus('Search applied. ' + visibleCount + ' ' + label + ' found for "' + input.value.trim() + '".', 'success');
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (submitButton) {
                submitButton.classList.add('is-loading');
                submitButton.disabled = true;
            }

            window.setTimeout(function () {
                runSearch(true);

                if (submitButton) {
                    submitButton.classList.remove('is-loading');
                    submitButton.disabled = false;
                }

                input.blur();
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
            runSearch(false);
        } else {
            hideStatus();
        }
    });
</script>

@endsection
