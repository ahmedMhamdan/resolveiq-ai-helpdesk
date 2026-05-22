@extends('layouts.app')

@section('title', 'Agents')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Agents</h1>
        <p class="page-subtitle">Manage support agents who handle tickets.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('agents.create') }}" class="btn btn-primary">
            + New Agent
        </a>
    </div>
</div>

<div class="table-card agents-table-card">
    <div class="table-head">
        <div>
            <h2>Agents</h2>
            <p class="page-subtitle">All support agents in the workspace.</p>
        </div>
    </div>

    <div class="table-wrap agents-table-wrap">
        <table class="agents-table">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Email</th>
                    <th class="tickets-col">Assigned Tickets</th>
                    <th class="tickets-col">Replies</th>
                    <th>Created</th>
                    <th class="users-center-col">Change Role</th>
                    <th class="users-center-col">Actions</th>
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
                        <td data-label="Agent">
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
                                    <small>Support Agent</small>
                                </div>
                            </div>
                        </td>

                        <td data-label="Email">
                            <span class="agent-email">{{ $agent->email }}</span>
                        </td>

                        <td class="tickets-col" data-label="Assigned Tickets">
                            <span class="badge open ticket-count-badge">
                                {{ $agent->assigned_tickets_count }}
                            </span>
                        </td>

                        <td class="tickets-col" data-label="Replies">
                            <span class="badge pending ticket-count-badge">
                                {{ $agent->ticket_replies_count }}
                            </span>
                        </td>

                        <td data-label="Created">
                            {{ $agent->created_at->format('M d, Y') }}
                        </td>

                        <td class="users-center-col" data-label="Change Role">
                            @if ($isCurrentUser)
                                <span class="role-badge role-user">
                                    Current Account
                                </span>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route('agents.makeUser', $agent) }}"
                                    class="users-role-form agents-downgrade-form"
                                    onsubmit="return confirm('Move this agent back to users? Assigned tickets will become unassigned.')"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="btn btn-sm btn-downgrade-user">
                                        Make User
                                    </button>
                                </form>
                            @endif
                        </td>

                        <td class="users-center-col" data-label="Actions">
                            <div class="row-actions agents-row-actions">
                                <a href="{{ route('agents.edit', $agent) }}" class="btn btn-sm btn-edit-soft">
                                    Edit
                                </a>

                                <form action="{{ route('agents.destroy', $agent) }}" method="POST" onsubmit="return confirm('Delete this agent?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger-soft">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state compact-empty-state">
                                <strong>No agents found.</strong>
                                <span>Create your first support agent to start assigning tickets.</span>
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
