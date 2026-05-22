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
                class="filters js-live-ticket-search"
                data-live-table=".unassigned-table"
                data-live-empty="#unassigned-empty-message"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search ticket..."
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
                        <tr class="live-ticket-row">
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="ticket-link">
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                    <span>{{ $ticket->title }}</span>
                                </a>
                            </td>

                            <td>
                                <div class="person">
                                    <span class="mini-avatar">
                                        @if ($ticket->user?->avatar_path)
                                            <img
                                                src="{{ method_exists($ticket->user, 'avatarUrl') ? $ticket->user->avatarUrl() : (str_starts_with($ticket->user->avatar_path, 'images/') ? asset($ticket->user->avatar_path) : asset('storage/' . $ticket->user->avatar_path)) }}"
                                                alt="{{ $ticket->user->name }} avatar"
                                            >
                                        @else
                                            <span class="avatar-fallback">?</span>
                                        @endif
                                    </span>

                                    <div>
                                        <strong>{{ $ticket->user?->name ?? 'Unknown' }}</strong>
                                        <br>
                                        <small>{{ $ticket->user?->email ?? 'No email' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="users-center-col">
                                {{ $ticket->department?->name ?? 'No department' }}
                            </td>

                            <td class="users-center-col">
                                <span class="badge {{ $ticket->status }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>

                            <td class="users-center-col">
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

                            <td class="users-center-col">
                                <input
                                    type="date"
                                    name="due_at"
                                    form="assign-ticket-{{ $ticket->id }}"
                                    value="{{ $ticket->due_at ? $ticket->due_at->format('Y-m-d') : '' }}"
                                    class="unassigned-due-date-input"
                                >
                            </td>

                            <td class="users-center-col">
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
