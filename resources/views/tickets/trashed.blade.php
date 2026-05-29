@extends('layouts.app')

@section('title', __('tickets.deleted_tickets'))

@section('content')
<div class="page-head">
    <div>
        <h1 data-auto-translate>{{ __('tickets.deleted_tickets') }}</h1>
        <p data-auto-translate>{{ __('tickets.restore_subtitle') }}</p>
    </div>

    <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-page-back" data-auto-translate>
        {{ __('tickets.back_to_tickets') }}
    </a>
</div>

<div class="table-card deleted-tickets-card" id="deleted-ticket-list">
    <div class="card-head">
        <div>
            <h2 data-auto-translate>{{ __('tickets.deleted_tickets') }}</h2>
            <p data-auto-translate>{{ __('tickets.tickets_moved_to_archive') }}</p>
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
                placeholder="{{ __('tickets.search_deleted') }}"
                data-auto-translate-attribute="placeholder"
                autocomplete="off"
                class="deleted-ticket-search-input"
            >

            <button type="submit" data-auto-translate>{{ __('common.search') }}</button>

            <button type="button" class="btn btn-secondary deleted-ticket-reset" hidden data-auto-translate>
                {{ __('common.reset') }}
            </button>
        </form>
    </div>

    <div class="table-wrap deleted-tickets-wrap">
        <table class="deleted-tickets-table">
            <thead>
                <tr>
                    <th class="col-ticket" data-auto-translate>{{ __('common.ticket') }}</th>
                    <th class="col-person" data-auto-translate>{{ __('common.requester') }}</th>
                    <th class="col-person" data-auto-translate>{{ __('common.agent') }}</th>
                    <th class="col-department" data-auto-translate>{{ __('common.department') }}</th>
                    <th class="col-status" data-auto-translate>{{ __('common.status') }}</th>
                    <th class="col-priority" data-auto-translate>{{ __('common.priority') }}</th>
                    <th class="col-date" data-auto-translate>{{ __('common.due_date') }}</th>
                    <th class="col-date" data-auto-translate>{{ __('tickets.deleted_at') }}</th>
                    <th class="col-actions" data-auto-translate>{{ __('common.actions') }}</th>
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
                        <td class="ticket-cell" data-label="{{ __('common.ticket') }}">
                            <strong class="ticket-number">#{{ $ticket->ticket_number }}</strong>
                            <div class="ticket-title">{{ $ticket->title }}</div>
                        </td>

                        <td data-label="{{ __('common.requester') }}">
                            <div class="person ticket-person">
                                <div class="mini-avatar">
                                    @if ($requesterAvatarUrl)
                                        <img src="{{ $requesterAvatarUrl }}" alt="{{ $requester?->name ?? __('common.requester') }} avatar">
                                    @else
                                        <span class="avatar-fallback">?</span>
                                    @endif
                                </div>

                                <div class="person-meta">
                                    <strong>{{ $requester?->name ?? __('common.unknown') }}</strong>
                                    <small data-auto-translate>{{ __('common.requester') }}</small>
                                </div>
                            </div>
                        </td>

                        <td data-label="{{ __('common.agent') }}">
                            @if ($agent)
                                <div class="person ticket-person">
                                    <div class="mini-avatar">
                                        @if ($agentAvatarUrl)
                                            <img src="{{ $agentAvatarUrl }}" alt="{{ $agent->name }} avatar">
                                        @else
                                            <span class="avatar-fallback">?</span>
                                        @endif
                                    </div>

                                    <div class="person-meta">
                                        <strong>{{ $agent->name }}</strong>
                                        <small data-auto-translate>{{ __('common.agent') }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="page-subtitle" data-auto-translate>{{ __('common.unassigned') }}</span>
                            @endif
                        </td>

                        <td class="department-cell" data-label="{{ __('common.department') }}">
                            {{ $ticket->department?->name ?? __('common.no_department') }}
                        </td>

                        <td data-label="{{ __('common.status') }}">
                            <span class="badge {{ $ticket->status }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>

                        <td data-label="{{ __('common.priority') }}">
                            <span class="priority {{ $ticket->priority ?? 'unset' }}">
                                {{ $ticket->priority ? ucfirst($ticket->priority) : __('common.not_set') }}
                            </span>
                        </td>

                        <td class="date-cell" data-label="{{ __('common.due_date') }}">
                            @if ($ticket->due_at)
                                <div class="due-date-info {{ $isOverdue ? 'overdue' : '' }}">
                                    <strong>{{ $ticket->due_at->format('M d, Y') }}</strong>
                                    <small>{{ $isOverdue ? __('common.overdue') : $ticket->due_at->diffForHumans() }}</small>
                                </div>
                            @else
                                <span class="page-subtitle" data-auto-translate>{{ __('common.not_set') }}</span>
                            @endif
                        </td>

                        <td class="date-cell" data-label="{{ __('tickets.deleted_at') }}">
                            {{ $ticket->deleted_at?->diffForHumans() }}
                        </td>

                        <td data-label="{{ __('common.actions') }}">
                            <div class="deleted-ticket-actions">
                                <form action="{{ route('tickets.restore', $ticket->id) }}" method="POST">
                                    @csrf

                                    <button type="submit" class="btn btn-sm btn-restore-soft" data-auto-translate>
                                        {{ __('tickets.restore') }}
                                    </button>
                                </form>

                                <form action="{{ route('tickets.forceDelete', $ticket->id) }}" method="POST" onsubmit="return confirm('{{ __('tickets.confirm_force_delete') }}')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger-soft btn-sm" data-auto-translate>
                                        {{ __('tickets.delete_forever') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" data-auto-translate>
                            {{ __('tickets.no_deleted_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p id="deleted-empty-message" class="live-search-empty" hidden data-auto-translate>
            {{ __('tickets.no_matching_deleted') }}
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
        const reset = form.querySelector('.deleted-ticket-reset');
        const table = document.querySelector(form.dataset.liveTable);
        const empty = document.querySelector(form.dataset.liveEmpty);

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

        function runSearch() {
            const term = input.value.replace(/\s+/g, ' ').trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(function (row) {
                const shouldShow = !term || getRowText(row).includes(term);

                row.classList.toggle('is-hidden', !shouldShow);

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
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            runSearch();
            input.blur();
        });

        if (reset) {
            reset.addEventListener('click', function () {
                input.value = '';
                runSearch();
                input.focus();
            });
        }

        if (input.value.trim()) {
            runSearch();
        }
    });
</script>

@endsection