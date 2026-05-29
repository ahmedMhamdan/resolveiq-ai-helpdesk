@extends('layouts.app')

@section('title', __('tickets.unassigned_tickets'))

@section('content')
    <div class="page-head">
        <div>
            <h1 data-auto-translate>{{ __('tickets.unassigned_tickets') }}</h1>
            <p class="page-subtitle" data-auto-translate>
                {{ __('tickets.unassigned_subtitle') }}
            </p>
        </div>

        <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-page-back" data-auto-translate>
            {{ __('tickets.back_to_tickets') }}
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
            <h2 data-auto-translate>{{ __('tickets.waiting_for_assignment') }}</h2>

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
                    placeholder="{{ __('tickets.search_unassigned') }}"
                    data-auto-translate-attribute="placeholder"
                    autocomplete="off"
                    class="js-live-ticket-input"
                >

                <button type="submit" data-auto-translate>{{ __('common.search') }}</button>

                <button type="button" class="btn btn-secondary js-live-ticket-reset" hidden data-auto-translate>
                    {{ __('common.reset') }}
                </button>
            </form>
        </div>

        <div class="table-wrap">
            <table class="unassigned-table">
                <thead>
                    <tr>
                        <th data-auto-translate>{{ __('common.ticket') }}</th>
                        <th data-auto-translate>{{ __('common.requester') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.department') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.status') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.priority') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.due_date') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('tickets.assign_agent') }}</th>
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
                                        <strong>{{ $ticket->user?->name ?? __('common.unknown') }}</strong>
                                        <br>
                                        <small>{{ $ticket->user?->email ?? __('common.no_email') }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="users-center-col">
                                {{ $ticket->department?->name ?? __('common.no_department') }}
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
                                    <option value="" @selected($ticket->priority === null) data-auto-translate>
                                        {{ __('common.not_set') }}
                                    </option>
                                    <option value="low" @selected($ticket->priority === 'low') data-auto-translate>{{ __('tickets.low') }}</option>
                                    <option value="medium" @selected($ticket->priority === 'medium') data-auto-translate>{{ __('tickets.medium') }}</option>
                                    <option value="high" @selected($ticket->priority === 'high') data-auto-translate>{{ __('tickets.high') }}</option>
                                    <option value="urgent" @selected($ticket->priority === 'urgent') data-auto-translate>{{ __('tickets.urgent') }}</option>
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
                                        <option value="" data-auto-translate>{{ __('tickets.select_agent') }}</option>

                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->id }}">
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="btn btn-sm btn-assign-agent" data-auto-translate>
                                        {{ __('tickets.assign') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty" data-auto-translate>
                                {{ __('tickets.no_unassigned_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <p id="unassigned-empty-message" class="live-search-empty" hidden data-auto-translate>
                {{ __('tickets.no_matching_unassigned') }}
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