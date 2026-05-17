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

@if (session('error'))
    <div class="flash-message flash-error">
        {{ session('error') }}
    </div>
@endif

<div class="table-card">
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
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($agents as $agent)
                    @php
                        $agentAvatar = '';

                        if ($agent->avatar_path) {
                            $agentAvatar = method_exists($agent, 'avatarUrl')
                                ? $agent->avatarUrl()
                                : (str_starts_with($agent->avatar_path, 'images/')
                                    ? asset($agent->avatar_path)
                                    : asset('storage/' . $agent->avatar_path));
                        }
                    @endphp

                    <tr>
                        <td>
                            <div class="person agent-person">
                                <span class="mini-avatar agent-avatar">
                                    @if ($agentAvatar)
                                        <img src="{{ $agentAvatar }}" alt="{{ $agent->name }} avatar">
                                    @else
                                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                                    @endif
                                </span>

                                <div class="person-meta">
                                    <strong>{{ $agent->name }}</strong>
                                    <small>Support Agent</small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="agent-email">{{ $agent->email }}</span>
                        </td>

                        <td class="tickets-col">
                            <span class="badge open ticket-count-badge">
                                {{ $agent->assigned_tickets_count }}
                            </span>
                        </td>

                        <td class="tickets-col">
                            <span class="badge pending ticket-count-badge">
                                {{ $agent->ticket_replies_count }}
                            </span>
                        </td>

                        <td>{{ $agent->created_at->format('M d, Y') }}</td>

                        <td>
                            <div class="row-actions">
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
                        <td colspan="6">
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
