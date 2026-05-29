@extends('layouts.app')

@section('title', __('tickets.title'))

@section('content')
    @php
        $currentRole = strtolower($role ?? auth()->user()?->role?->name ?? 'user');
        $isAdmin = $currentRole === 'admin';
        $isAgent = $currentRole === 'agent';
        $isUser = $currentRole === 'user';

        $showRequesterColumn = $isAdmin || $isAgent;
        $showAgentColumn = $isAdmin;
        $showPriorityColumn = $isAdmin || $isAgent;

        $columnsCount = 5;

        if ($showRequesterColumn) {
            $columnsCount++;
        }

        if ($showAgentColumn) {
            $columnsCount++;
        }

        if ($showPriorityColumn) {
            $columnsCount++;
        }
    @endphp

    <div class="page-actions tickets-top-actions">
        @if ($isAdmin)
            <a href="{{ route('tickets.trashed') }}" class="btn btn-deleted-tickets" data-auto-translate>
                {{ __('tickets.deleted_tickets') }}
            </a>
        @endif
        @if ($role === 'admin')
        <a href="{{ route('tickets.overdue') }}" class="btn btn-overdue-tickets" data-auto-translate>
            {{ __('tickets.overdue_tickets') }}
        </a>
        @endif
        @if ($isAdmin || $isUser)
            <a href="{{ route('tickets.create') }}" class="btn btn-primary new-ticket-btn" data-auto-translate>
                {{ __('tickets.new_ticket') }}
            </a>
        @endif
        @if ($role === 'admin')
        <a href="{{ route('tickets.unassigned') }}" class="btn btn-unassigned-tickets" data-auto-translate>
            {{ __('tickets.unassigned_tickets') }}
        </a>
    @endif
    </div>

    <section class="card table-card tickets-index-card">
        <div class="table-head">
            <form class="filters" method="GET" action="{{ route('tickets.index') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('tickets.search_placeholder') }}" data-auto-translate-attribute="placeholder">

                <select name="status">
                    <option value="">{{ __('tickets.all_status') }}</option>
                    <option value="open" @selected(request('status') === 'open') data-auto-translate>{{ __('tickets.open') }}</option>
                    <option value="pending" @selected(request('status') === 'pending') data-auto-translate>{{ __('tickets.pending') }}</option>
                    <option value="solved" @selected(request('status') === 'solved') data-auto-translate>{{ __('tickets.solved') }}</option>
                    <option value="closed" @selected(request('status') === 'closed') data-auto-translate>{{ __('tickets.closed') }}</option>
                </select>

                @if ($showPriorityColumn)
                    <select name="priority">
                        <option value="">{{ __('tickets.all_priority') }}</option>
                        <option value="low" @selected(request('priority') === 'low') data-auto-translate>{{ __('tickets.low') }}</option>
                        <option value="medium" @selected(request('priority') === 'medium') data-auto-translate>{{ __('tickets.medium') }}</option>
                        <option value="high" @selected(request('priority') === 'high') data-auto-translate>{{ __('tickets.high') }}</option>
                        <option value="urgent" @selected(request('priority') === 'urgent') data-auto-translate>{{ __('tickets.urgent') }}</option>
                    </select>
                @endif

                <button type="submit" data-auto-translate>{{ __('common.filter') }}</button>

                @if(request()->hasAny(['search', 'status', 'priority']))
                    <a class="btn secondary" href="{{ route('tickets.index') }}" data-auto-translate>{{ __('common.reset') }}</a>
                @endif

            </form>
        </div>

        <table class="tickets-table">
            <thead>
                <tr>
                    <th data-auto-translate>{{ __('common.ticket') }}</th>

                    @if ($showRequesterColumn)
                        <th data-auto-translate>{{ __('common.requester') }}</th>
                    @endif

                    @if ($showAgentColumn)
                        <th data-auto-translate>{{ __('common.agent') }}</th>
                    @endif

                    <th data-auto-translate>{{ __('common.department') }}</th>
                    <th data-auto-translate>{{ __('common.status') }}</th>

                    @if ($showPriorityColumn)
                        <th data-auto-translate>{{ __('common.priority') }}</th>
                    @endif

                    <th data-auto-translate>{{ __('common.due_date') }}</th>
                    <th data-auto-translate>{{ __('common.updated') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td data-label="{{ __('common.ticket') }}">
                            <a class="ticket-link" href="{{ route('tickets.show', $ticket) }}">
                                <strong>#{{ $ticket->ticket_number }}</strong>
                                <span>{{ $ticket->title }}</span>
                            </a>
                        </td>

                        @if ($showRequesterColumn)
                            <td data-label="{{ __('common.requester') }}">
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
                                    <div class="mini-avatar">
                                        @if ($requesterAvatarUrl)
                                            <img src="{{ $requesterAvatarUrl }}" alt="{{ $requester->name }} avatar">
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

                        @if ($showAgentColumn)
                            <td data-label="{{ __('common.agent') }}">
                                @if($ticket->agent)
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

                                    <div class="person">
                                        <div class="mini-avatar">
                                            @if ($agentAvatarUrl)
                                                <img src="{{ $agentAvatarUrl }}" alt="{{ $agent->name }} avatar">
                                            @else
                                                <span class="avatar-fallback">?</span>
                                            @endif
                                        </div>

                                        <div>
                                            <strong>{{ $agent->name }}</strong><br>
                                            <small data-auto-translate>{{ __('common.agent') }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="page-subtitle" data-auto-translate>{{ __('common.unassigned') }}</span>
                                @endif
                            </td>
                        @endif

                        <td data-label="{{ __('common.department') }}">{{ $ticket->department?->name ?? __('common.no_department') }}</td>
                        <td data-label="{{ __('common.status') }}"><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>

                        @if ($showPriorityColumn)
                            <td data-label="{{ __('common.priority') }}">
                                <span class="priority {{ $ticket->priority ?? 'unset' }}">
                                    {{ $ticket->priority ? ucfirst($ticket->priority) : __('common.not_set') }}
                                </span>
                            </td>
                        @endif

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
                                <span class="page-subtitle" data-auto-translate>{{ __('common.not_set') }}</span>
                            @endif
                        </td>

                        <td data-label="{{ __('common.updated') }}">{{ $ticket->updated_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $columnsCount }}" data-label="{{ __('common.empty') }}">
                            <div class="empty" data-auto-translate>{{ __('tickets.no_tickets_found') }}</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $tickets->links('vendor.pagination.resolveiq') }}
        </div>
    </section>
@endsection
