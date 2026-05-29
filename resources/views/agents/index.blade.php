@extends('layouts.app')

@section('title', __('agents.title'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('agents.title') }}</h1>
        <p class="page-subtitle">{{ __('agents.subtitle') }}</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('agents.create') }}" class="btn btn-primary">
            {{ __('agents.new_agent') }}
        </a>
    </div>
</div>

<div class="table-card agents-table-card">
    <div class="table-head">
        <div>
            <h2>{{ __('agents.table_heading') }}</h2>
            <p class="page-subtitle">{{ __('agents.table_subtitle') }}</p>
        </div>
    </div>

    <div class="table-wrap agents-table-wrap">
        <table class="agents-table">
            <thead>
                <tr>
                    <th>{{ __('agents.agent_th') }}</th>
                    <th>{{ __('agents.email_th') }}</th>
                    <th class="tickets-col">{{ __('agents.assigned_tickets') }}</th>
                    <th class="tickets-col">{{ __('agents.replies') }}</th>
                    <th>{{ __('agents.created') }}</th>
                    <th class="users-center-col">{{ __('agents.change_role') }}</th>
                    <th class="users-center-col">{{ __('agents.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($agents as $agent)
                    @php
                        $agentAvatar = null;
                        $isCurrentUser = auth()->id() === $agent->id;

                        if ($agent->avatar_path) {
                            $agentAvatar = method_exists($agent, 'avatarUrl')
                                ? $agent->avatarUrl()
                                : (str_starts_with($agent->avatar_path, 'images/')
                                    ? asset($agent->avatar_path)
                                    : asset('storage/' . $agent->avatar_path));
                        }
                    @endphp

                    <tr>
                        <td data-label="{{ __('agents.agent_th') }}">
                            <div class="person agent-person">
                                <span class="mini-avatar agent-avatar">
                                    @if ($agentAvatar)
                                        <img src="{{ $agentAvatar }}" alt="{{ $agent->name }} avatar">
                                    @else
                                        <span class="avatar-fallback">?</span>
                                    @endif
                                </span>

                                <div class="person-meta">
                                    <strong>{{ $agent->name }}</strong>
                                    <small>{{ __('agents.support_role') }}</small>
                                </div>
                            </div>
                        </td>

                        <td data-label="{{ __('agents.email_th') }}">
                            <span class="agent-email">{{ $agent->email }}</span>
                        </td>

                        <td class="tickets-col" data-label="{{ __('agents.assigned_tickets') }}">
                            <span class="badge open ticket-count-badge">
                                {{ $agent->assigned_tickets_count }}
                            </span>
                        </td>

                        <td class="tickets-col" data-label="{{ __('agents.replies') }}">
                            <span class="badge pending ticket-count-badge">
                                {{ $agent->ticket_replies_count }}
                            </span>
                        </td>

                        <td data-label="{{ __('agents.created') }}">
                            {{ $agent->created_at->format('M d, Y') }}
                        </td>

                        <td class="users-center-col" data-label="{{ __('agents.change_role') }}">
                            @if ($isCurrentUser)
                                <span class="role-badge role-user">
                                    {{ __('agents.current_account') }}
                                </span>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route('agents.makeUser', $agent) }}"
                                    class="users-role-form agents-downgrade-form"
                                    onsubmit="return confirm('{{ __('agents.confirm_make_user') }}')"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="btn btn-sm btn-downgrade-user">
                                        {{ __('agents.make_user') }}
                                    </button>
                                </form>
                            @endif
                        </td>

                        <td class="users-center-col" data-label="{{ __('agents.actions') }}">
                            <div class="row-actions agents-row-actions">
                                <a href="{{ route('agents.edit', $agent) }}" class="btn btn-sm btn-edit-soft">
                                    {{ __('agents.edit_btn') }}
                                </a>

                                <form action="{{ route('agents.destroy', $agent) }}" method="POST" onsubmit="return confirm('{{ __('agents.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger-soft">
                                        {{ __('agents.delete_btn') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state compact-empty-state">
                                <strong>{{ __('agents.no_agents') }}</strong>
                                <span>{{ __('agents.no_agents_desc') }}</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $agents->links('vendor.pagination.resolveiq') }}
    </div>
</div>
@endsection