<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Notifications\TicketEventNotification;


class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->currentUser($request);
        $role = $this->roleName($user);

        $tickets = $this->ticketQueryFor($role, $user)
            ->with(['user', 'agent', 'department'])
            ->when($request->search, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->priority, fn (Builder $query, string $priority) => $query->where('priority', $priority))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('tickets.index', compact('tickets', 'role'));
    }

    public function show(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);
        $role = $this->roleName($user);

        abort_unless($this->canViewTicket($user, $ticket), 403);

        $ticket->load([
            'user',
            'agent',
            'department',
            'replies.user',
            'replies.attachments',
            'activityLogs.user',
        ]);

        return view('tickets.show', compact('ticket', 'role'));
    }

    public function create(Request $request)
    {
        $user = $this->currentUser($request);
        $role = $this->roleName($user);

        $departments = Department::query()
            ->orderBy('name', 'asc')
            ->get();

        $agents = collect();

        if ($role === 'admin') {
            $agents = $this->agentUsersQuery()
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('tickets.create', compact('departments', 'agents', 'role'));
    }

    public function store(Request $request)
    {
        $user = $this->currentUser($request);
        $role = $this->roleName($user);

        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'department_id' => ['required', 'exists:departments,id'],
        ];

        if ($role === 'admin') {
            $rules['agent_id'] = ['nullable', 'exists:users,id'];
            $rules['priority'] = ['nullable', 'in:low,medium,high,urgent'];
            $rules['due_at'] = ['nullable', 'date'];
        }

        $data = $request->validate($rules);

        if (! empty($data['agent_id']) && ! $this->isAgentUser((int) $data['agent_id'])) {
            return back()
                ->withErrors(['agent_id' => 'Selected user is not a support agent.'])
                ->withInput();
        }

        $ticket = Ticket::create([
            'ticket_number' => $this->makeTicketNumber(),
            'user_id' => $user->id,
            'agent_id' => $role === 'admin' ? ($data['agent_id'] ?? null) : null,
            'department_id' => $data['department_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => 'open',
            'priority' => $role === 'admin' ? ($data['priority'] ?? null) : null,
            'due_at' => $role === 'admin' ? ($data['due_at'] ?? null) : null,
        ]);

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => 'Ticket created',
            'old_value' => null,
            'new_value' => 'open',
        ]);
        $ticket->loadMissing('agent');

        $admins = User::query()
        ->whereHas('role', fn ($query) => $query->where('name', 'admin'))
        ->whereKeyNot($user->id)
        ->get();

    foreach ($admins as $admin) {
        $admin->notify(new TicketEventNotification(
            'New ticket created',
            "{$user->name} created ticket {$ticket->ticket_number}.",
            $ticket,
            'created',
            $user
        ));
    }

    if ($ticket->agent_id && (int) $ticket->agent_id !== (int) $user->id) {
        $ticket->agent?->notify(new TicketEventNotification(
            'New ticket assigned',
            "Ticket {$ticket->ticket_number} was assigned to you.",
            $ticket,
            'assigned',
            $user
        ));
    }
        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    public function edit(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);
        $role = $this->roleName($user);

        abort_unless($this->canManageTicket($user, $ticket), 403);

        $departments = Department::query()
            ->orderBy('name', 'asc')
            ->get();

        $agents = collect();

        if ($role === 'admin') {
            $agents = $this->agentUsersQuery()
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('tickets.edit', compact('ticket', 'departments', 'agents', 'role'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);
        $role = $this->roleName($user);

        abort_unless($this->canManageTicket($user, $ticket), 403);

        $rules = [
            'department_id' => ['required', 'exists:departments,id'],
            'status' => ['required', 'in:open,pending,solved,closed'],
        ];

        if ($role === 'admin') {
            $rules['agent_id'] = ['nullable', 'exists:users,id'];
            $rules['priority'] = ['nullable', 'in:low,medium,high,urgent'];
            $rules['due_at'] = ['nullable', 'date'];
        }

        $data = $request->validate($rules);

        if (! empty($data['agent_id']) && ! $this->isAgentUser((int) $data['agent_id'])) {
            return back()
                ->withErrors(['agent_id' => 'Selected user is not a support agent.'])
                ->withInput();
        }

        $oldStatus = $ticket->status;
        $oldPriority = $ticket->priority;
        $oldAgentId = $ticket->agent_id;

        $resolvedAt = $ticket->resolved_at;
        $closedAt = $ticket->closed_at;

        if ($data['status'] === 'solved' && $ticket->status !== 'solved') {
            $resolvedAt = now();
        }

        if ($data['status'] === 'closed' && $ticket->status !== 'closed') {
            $closedAt = now();
        }

        if (! in_array($data['status'], ['solved', 'closed'], true)) {
            $resolvedAt = null;
            $closedAt = null;
        }

        $updateData = [
            'department_id' => $data['department_id'],
            'status' => $data['status'],
            'resolved_at' => $resolvedAt,
            'closed_at' => $closedAt,
        ];

        if ($role === 'admin') {
            $updateData['agent_id'] = $data['agent_id'] ?? null;
            $updateData['priority'] = $data['priority'] ?? null;
            $updateData['due_at'] = $data['due_at'] ?? null;
        }

        $ticket->fill($updateData);
        $ticket->save();
        $ticket->refresh();

        if ($oldStatus !== $ticket->status) {
            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Status changed',
                'old_value' => $oldStatus,
                'new_value' => $ticket->status,
            ]);
        }

        if ($oldPriority !== $ticket->priority) {
            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Priority changed',
                'old_value' => $oldPriority ?? 'Not set',
                'new_value' => $ticket->priority ?? 'Not set',
            ]);
        }

        if ($role === 'admin' && (int) $oldAgentId !== (int) $ticket->agent_id) {
            $oldAgentName = 'Unassigned';

            if ($oldAgentId) {
                $oldAgentName = User::query()
                    ->whereKey($oldAgentId)
                    ->value('name') ?? 'Unassigned';
            }

            $ticket->loadMissing('agent');

            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Agent changed',
                'old_value' => $oldAgentName,
                'new_value' => $ticket->agent?->name ?? 'Unassigned',
            ]);
        }

        if ($oldStatus === $ticket->status && $oldPriority === $ticket->priority && (int) $oldAgentId === (int) $ticket->agent_id) {
            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Ticket updated',
                'old_value' => null,
                'new_value' => null,
            ]);
        }

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }
    public function overdue(Request $request)
    {
        $user = $this->currentUser($request);

        if ($this->roleName($user) !== 'admin') {
            abort(403);
        }

        $search = $request->query('search');

        /** @var Builder $ticketsQuery */
        $ticketsQuery = Ticket::query();

        $tickets = $ticketsQuery
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['solved', 'closed'])
            ->with(['user', 'agent', 'department'])
            ->when($search, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->orderBy('due_at', 'asc')
            ->paginate(8)
            ->withQueryString();

        return view('tickets.overdue', compact('tickets'));
    }
    public function unassigned(Request $request)
    {
        $user = $this->currentUser($request);

        abort_unless($this->roleName($user) === 'admin', 403);

        $tickets = Ticket::query()
            ->where('agent_id', '=', null)
            ->with(['user', 'department'])
            ->when($request->search, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $agents = $this->agentUsersQuery()
            ->orderBy('name', 'asc')
            ->get();

        return view('tickets.unassigned', compact('tickets', 'agents'));
    }

    public function assignAgent(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);

        abort_unless($this->roleName($user) === 'admin', 403);

        $data = $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
        ]);

        if (! $this->isAgentUser((int) $data['agent_id'])) {
            return back()
                ->withErrors(['agent_id' => 'Selected user is not a support agent.'])
                ->withInput();
        }

        $oldAgentName = $ticket->agent?->name ?? 'Unassigned';
        $oldPriority = $ticket->priority;

        $ticket->agent_id = $data['agent_id'];
        $ticket->priority = $data['priority'] ?? null;
        $ticket->save();

        $ticket->loadMissing('agent');

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => 'Agent assigned',
            'old_value' => $oldAgentName,
            'new_value' => $ticket->agent?->name ?? 'Unassigned',
        ]);

        if ($ticket->agent && (int) $ticket->agent_id !== (int) $user->id) {
        $ticket->agent->notify(new TicketEventNotification(
            'Ticket assigned to you',
            "You were assigned to ticket {$ticket->ticket_number}.",
            $ticket,
            'assigned',
            $user
        ));
    }
        if ($oldPriority !== $ticket->priority) {
            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Priority changed',
                'old_value' => $oldPriority ?? 'Not set',
                'new_value' => $ticket->priority ?? 'Not set',
            ]);
        }

        return back()->with('success', 'Ticket assigned successfully.');
    }

    public function trashed(Request $request)
    {
        $user = $this->currentUser($request);

        abort_unless($this->roleName($user) === 'admin', 403);

        $tickets = Ticket::onlyTrashed()
            ->with(['user', 'agent', 'department'])
            ->latest('deleted_at')
            ->paginate(10);

        return view('tickets.trashed', compact('tickets'));
    }
    public function reopen(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);

        abort_unless($this->canManageTicket($user, $ticket), 403);

        if (! in_array($ticket->status, ['solved', 'closed'], true)) {
            return back()->with('error', 'Only solved or closed tickets can be reopened.');
        }

        $oldStatus = $ticket->status;

        $ticket->fill([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);

        $ticket->save();

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => 'Ticket reopened',
            'old_value' => $oldStatus,
            'new_value' => 'open',
        ]);

        return back()->with('success', 'Ticket reopened successfully.');
    }
    public function close(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);
        $role = $this->roleName($user);

        $canClose = $role === 'admin'
            || ($role === 'agent' && (int) $ticket->agent_id === (int) $user->id);

        abort_unless($canClose, 403);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'Ticket is already closed.');
        }

        $oldStatus = $ticket->status;

        $ticket->fill([
            'status' => 'closed',
            'resolved_at' => $ticket->resolved_at ?? now(),
            'closed_at' => now(),
        ]);

        $ticket->save();

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => 'Ticket closed',
            'old_value' => $oldStatus,
            'new_value' => 'closed',
        ]);

        return back()->with('success', 'Ticket closed successfully.');
    }
    public function destroy(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);

        abort_unless($this->roleName($user) === 'admin', 403);

        Ticket::query()
            ->whereKey($ticket->getKey())
            ->delete();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Ticket moved to deleted tickets.');
    }

    public function restore(Request $request, int|string $id)
    {
        $user = $this->currentUser($request);

        abort_unless($this->roleName($user) === 'admin', 403);

        $ticket = Ticket::onlyTrashed()->findOrFail($id);
        $ticket->restore();

        return redirect()
            ->route('tickets.trashed')
            ->with('success', 'Ticket restored successfully.');
    }

    public function forceDelete(Request $request, int|string $id)
    {
        $user = $this->currentUser($request);

        abort_unless($this->roleName($user) === 'admin', 403);

        $ticket = Ticket::onlyTrashed()->findOrFail($id);
        $ticket->forceDelete();

        return redirect()
            ->route('tickets.trashed')
            ->with('success', 'Ticket permanently deleted.');
    }

    private function currentUser(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function ticketQueryFor(string $role, User $user): Builder
    {
        $query = Ticket::query();

        if ($role === 'agent') {
            $query->where('agent_id', $user->id);
        }

        if ($role === 'user') {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function canViewTicket(User $user, Ticket $ticket): bool
    {
        $role = $this->roleName($user);

        return $role === 'admin'
            || ($role === 'agent' && $ticket->agent_id === $user->id)
            || ($role === 'user' && $ticket->user_id === $user->id);
    }

    private function canManageTicket(User $user, Ticket $ticket): bool
    {
        $role = $this->roleName($user);

        return $role === 'admin'
            || ($role === 'agent' && $ticket->agent_id === $user->id);
    }

    private function roleName(User $user): string
    {
        return strtolower($user->role?->name ?? 'user');
    }

    private function agentUsersQuery(): Builder
    {
        return User::query()
            ->whereHas('role', function (Builder $query) {
                $query->where('name', 'agent');
            });
    }

    private function isAgentUser(int $userId): bool
    {
        return $this->agentUsersQuery()
            ->whereKey($userId)
            ->exists();
    }

    private function makeTicketNumber(): string
    {
        $baseNumber = ((int) Ticket::withTrashed()->latest('id')->value('id')) + 1001;

        do {
            $ticketNumber = 'RIQ-' . $baseNumber;
            $baseNumber++;
        } while (Ticket::withTrashed()->where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }
}
