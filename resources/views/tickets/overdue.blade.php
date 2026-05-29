@extends('layouts.app')

@section('title', __('tickets.overdue_tickets'))

@section('content')
    <div class="page-head">
        <div>
            <h1 data-auto-translate>{{ __('tickets.overdue_tickets') }}</h1>
            <p class="page-subtitle" data-auto-translate>
                {{ __('tickets.overdue_subtitle') }}
            </p>
        </div>
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-page-back" data-auto-translate>
            {{ __('tickets.back_to_tickets') }}
        </a>
    </div>

    <div class="table-card overdue-table-card" id="overdue-ticket-list">
        <div class="table-head">
            <h2 data-auto-translate>{{ __('tickets.needs_attention') }}</h2>

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
                    placeholder="{{ __('tickets.search_overdue') }}"
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
            <table class="overdue-table">
                <thead>
                    <tr>
                        <th data-auto-translate>{{ __('common.ticket') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.requester') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.agent') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.department') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.due_date') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.status') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.priority') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('common.actions') }}</th>
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
                                            <img src="{{ $requesterAvatarUrl }}" alt="{{ $requester?->name ?? __('common.requester') }} avatar">
                                        @else
                                            <span class="avatar-fallback">?</span>
                                        @endif
                                    </span>

                                    <div>
                                        <strong>{{ $requester?->name ?? __('common.unknown') }}</strong>
                                        <br>
                                        <small>{{ $requester?->email ?? __('common.no_email') }}</small>
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
                                    <span class="role-badge role-user" data-auto-translate>
                                        {{ __('common.unassigned') }}
                                    </span>
                                @endif
                            </td>

                            <td class="users-center-col">
                                {{ $ticket->department?->name ?? __('common.no_department') }}
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
                                    <span class="priority unset" data-auto-translate>
                                        {{ __('common.not_set') }}
                                    </span>
                                @endif
                            </td>

                            <td class="users-center-col">
                                <div class="row-actions users-role-actions">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-view-ticket" data-auto-translate>
                                        {{ __('tickets.view') }}
                                    </a>

                                    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-sm" data-auto-translate>
                                        {{ __('tickets.manage') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty" data-auto-translate>
                                {{ __('tickets.no_overdue_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <p id="overdue-empty-message" class="live-search-empty" hidden data-auto-translate>
                {{ __('tickets.no_matching_overdue') }}
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