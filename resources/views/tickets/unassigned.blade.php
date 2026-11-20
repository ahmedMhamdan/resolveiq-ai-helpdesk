@extends('layouts.app')

@section('title', __('tickets.unassigned_tickets'))

@section('content')
@php
    $searchActive = request()->string('search', strict: true)->trim()->isNotEmpty();
@endphp
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
                class="filters"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request()->string('search', strict: true) }}"
                    placeholder="{{ __('tickets.search_unassigned') }}"
                    data-auto-translate-attribute="placeholder"
                    autocomplete="off"
                >

                <button type="submit" data-auto-translate>{{ __('common.search') }}</button>

                @if ($searchActive)
                    <a href="{{ route('tickets.unassigned') }}" class="btn btn-secondary js-live-ticket-reset" data-auto-translate>
                    {{ __('common.reset') }}
                </a>
                @endif
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
                        <tr>
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
                                {{ __($searchActive ? 'tickets.no_matching_unassigned' : 'tickets.no_unassigned_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $tickets->links('vendor.pagination.resolveiq') }}
    </div>

@endsection