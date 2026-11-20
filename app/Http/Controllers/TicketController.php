<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\TicketEventNotification;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->currentUser($request);
        $role = $user->roleName();

        $tickets = Ticket::visibleTo($user)
            ->with(['user', 'agent', 'department'])
            ->matchingSearch($request->string('search', strict: true)->trim()->toString())
            ->when($request->string('status', strict: true)->toString(), fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->string('priority', strict: true)->toString(), fn (Builder $query, string $priority) => $query->where('priority', $priority))
            ->latest()
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('tickets.index', compact('tickets', 'role'));
    }

    public function show(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);
        $role = $user->roleName();

        abort_unless($user->can('view', $ticket), 403);

        $ticket->load([
            'user',
            'agent',
            'department',
            'replies' => fn ($replies) => $replies->visibleTo($user)->with(['user', 'attachments']),
            'activityLogs' => fn ($logs) => $logs->visibleTo($user)->with('user'),
        ]);

        return view('tickets.show', compact('ticket', 'role'));
    }

    public function create(Request $request)
    {
        $user = $this->currentUser($request);
        $role = $user->roleName();

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
        $role = $user->roleName();

        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
        ];

        if ($role === 'admin') {
            $rules['agent_id'] = ['nullable', 'integer', 'exists:users,id'];
            $rules['priority'] = ['nullable', 'in:low,medium,high,urgent'];
            $rules['due_at'] = ['nullable', 'date'];
        }

        $data = $request->validate($rules);

        if (! empty($data['agent_id']) && ! $this->isAgentUser((int) $data['agent_id'])) {
            return back()
                ->withErrors(['agent_id' => 'Selected user is not a support agent.'])
                ->withInput();
        }

        $ticket = Ticket::createWithNumber($this->makeTicketNumber(...), [
            'user_id' => $user->id,
            'agent_id' => $role === 'admin' ? ($data['agent_id'] ?? null) : null,
            'department_id' => $data['department_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => 'open',
            'priority' => $role === 'admin' ? ($data['priority'] ?? null) : null,
            'due_at' => $role === 'admin' ? ($data['due_at'] ?? null) : null,
        ], 'Ticket created');

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
        $role = $user->roleName();

        abort_unless($user->can('update', $ticket), 403);

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
        $role = $user->roleName();

        abort_unless($user->can('update', $ticket), 403);

        $rules = [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'status' => ['required', 'in:open,pending,solved,closed'],
        ];

        if ($role === 'admin') {
            $rules['agent_id'] = ['nullable', 'integer', 'exists:users,id'];
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
        $oldDueAt = $ticket->due_at?->copy();

        $resolvedAt = in_array($data['status'], ['solved', 'closed'], true) ? ($ticket->resolved_at ?? now()) : null;
        $closedAt = $data['status'] === 'closed' ? ($ticket->closed_at ?? now()) : null;

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
        $ticket->loadMissing(['user', 'agent']);

        $dueDateChanged = ($oldDueAt?->toDateTimeString()) !== ($ticket->due_at?->toDateTimeString());

        if ($oldStatus !== $ticket->status) {
            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Status changed',
                'old_value' => $oldStatus,
                'new_value' => $ticket->status,
            ]);

            if ((int) $ticket->user_id !== (int) $user->id) {
                $ticket->user?->notify(new TicketEventNotification(
                    'Ticket status updated',
                    "Ticket {$ticket->ticket_number} status changed from {$oldStatus} to {$ticket->status}.",
                    $ticket,
                    'status',
                    $user
                ));
            }

            if ($ticket->agent && (int) $ticket->agent_id !== (int) $user->id) {
                $ticket->agent->notify(new TicketEventNotification(
                    'Assigned ticket status updated',
                    "Ticket {$ticket->ticket_number} status changed from {$oldStatus} to {$ticket->status}.",
                    $ticket,
                    'status',
                    $user
                ));
            }
        }

        if ($oldPriority !== $ticket->priority) {
            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Priority changed',
                'old_value' => $oldPriority ?? 'Not set',
                'new_value' => $ticket->priority ?? 'Not set',
            ]);

            if ($ticket->agent && (int) $ticket->agent_id !== (int) $user->id) {
                $ticket->agent->notify(new TicketEventNotification(
                    'Ticket priority updated',
                    "Ticket {$ticket->ticket_number} priority is now " . ($ticket->priority ?? 'not set') . '.',
                    $ticket,
                    'priority',
                    $user
                ));
            }
        }

        if ($dueDateChanged) {
            $oldDueText = $oldDueAt ? $oldDueAt->format('M d, Y - h:i A') : 'Not set';
            $newDueText = $ticket->due_at ? $ticket->due_at->format('M d, Y - h:i A') : 'Not set';

            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Due date changed',
                'old_value' => $oldDueText,
                'new_value' => $newDueText,
            ]);

            if ((int) $ticket->user_id !== (int) $user->id) {
                $ticket->user?->notify(new TicketEventNotification(
                    "Ticket {$ticket->ticket_number} due date updated",
                    "The due date changed from {$oldDueText} to {$newDueText}.",
                    $ticket,
                    'due_date',
                    $user
                ));
            }

            if ($ticket->agent && (int) $ticket->agent_id !== (int) $user->id) {
                $ticket->agent->notify(new TicketEventNotification(
                    "Ticket {$ticket->ticket_number} due date updated",
                    "The due date changed from {$oldDueText} to {$newDueText}.",
                    $ticket,
                    'due_date',
                    $user
                ));
            }
        }

        if ($role === 'admin' && (int) $oldAgentId !== (int) $ticket->agent_id) {
            $oldAgentName = 'Unassigned';

            if ($oldAgentId) {
                $oldAgentName = User::query()
                    ->whereKey($oldAgentId)
                    ->value('name') ?? 'Unassigned';
            }

            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Agent changed',
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
        }

        if (
            $oldStatus === $ticket->status
            && $oldPriority === $ticket->priority
            && (int) $oldAgentId === (int) $ticket->agent_id
            && ! $dueDateChanged
        ) {
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

        if ($user->roleName() !== 'admin') {
            abort(403);
        }

        /** @var Builder $ticketsQuery */
        $ticketsQuery = Ticket::query();

        $tickets = $ticketsQuery
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['solved', 'closed'])
            ->with(['user', 'agent', 'department'])
            ->matchingSearch($request->string('search', strict: true)->trim()->toString())
            ->orderBy('due_at', 'asc')
            ->paginate(8)
            ->withQueryString();

        return view('tickets.overdue', compact('tickets'));
    }

    public function unassigned(Request $request)
    {
        $user = $this->currentUser($request);

        abort_unless($user->roleName() === 'admin', 403);

        $tickets = Ticket::query()
            ->where('agent_id', '=', null)
            ->with(['user', 'department'])
            ->matchingSearch($request->string('search', strict: true)->trim()->toString())
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

        abort_unless($user->roleName() === 'admin', 403);

        $data = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'due_at' => ['nullable', 'date'],
        ]);

        if (! $this->isAgentUser((int) $data['agent_id'])) {
            return back()
                ->withErrors(['agent_id' => 'Selected user is not a support agent.'])
                ->withInput();
        }

        $oldAgentId = $ticket->agent_id;
        $oldAgentName = $oldAgentId
            ? (User::query()->whereKey($oldAgentId)->value('name') ?? 'Unassigned')
            : 'Unassigned';
        $oldPriority = $ticket->priority;
        $oldDueAt = $ticket->due_at?->copy();

        $ticket->agent_id = $data['agent_id'];
        $ticket->priority = $data['priority'] ?? null;
        $ticket->due_at = $data['due_at'] ?? null;
        $ticket->save();

        $ticket->refresh();
        $ticket->load(['user', 'agent']);

        if ((int) $oldAgentId !== (int) $ticket->agent_id) {
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
        }

        if ($oldPriority !== $ticket->priority) {
            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Priority changed',
                'old_value' => $oldPriority ?? 'Not set',
                'new_value' => $ticket->priority ?? 'Not set',
            ]);

            if ($ticket->agent && (int) $ticket->agent_id !== (int) $user->id) {
                $ticket->agent->notify(new TicketEventNotification(
                    'Ticket priority updated',
                    "Ticket {$ticket->ticket_number} priority is now " . ($ticket->priority ?? 'not set') . '.',
                    $ticket,
                    'priority',
                    $user
                ));
            }
        }

        if (($oldDueAt?->toDateTimeString()) !== ($ticket->due_at?->toDateTimeString())) {
            $oldDueText = $oldDueAt ? $oldDueAt->format('M d, Y - h:i A') : 'Not set';
            $newDueText = $ticket->due_at ? $ticket->due_at->format('M d, Y - h:i A') : 'Not set';

            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'Due date changed',
                'old_value' => $oldDueText,
                'new_value' => $newDueText,
            ]);

            if ((int) $ticket->user_id !== (int) $user->id) {
                $ticket->user?->notify(new TicketEventNotification(
                    "Ticket {$ticket->ticket_number} due date updated",
                    "The due date changed from {$oldDueText} to {$newDueText}.",
                    $ticket,
                    'due_date',
                    $user
                ));
            }

            if ($ticket->agent && (int) $ticket->agent_id !== (int) $user->id) {
                $ticket->agent->notify(new TicketEventNotification(
                    "Ticket {$ticket->ticket_number} due date updated",
                    "The due date changed from {$oldDueText} to {$newDueText}.",
                    $ticket,
                    'due_date',
                    $user
                ));
            }
        }

        return back()->with('success', 'Ticket assigned successfully.');
    }

    public function trashed(Request $request)
    {
        $user = $this->currentUser($request);

        abort_unless($user->roleName() === 'admin', 403);

        $tickets = Ticket::onlyTrashed()
            ->matchingSearch($request->string('search', strict: true)->trim()->toString())
            ->with(['user', 'agent', 'department'])
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('tickets.trashed', compact('tickets'));
    }

    public function reopen(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);

        abort_unless($user->can('update', $ticket), 403);

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

        $ticket->loadMissing(['user', 'agent']);

        if ((int) $ticket->user_id !== (int) $user->id) {
            $ticket->user?->notify(new TicketEventNotification(
                'Ticket reopened',
                "Ticket {$ticket->ticket_number} has been reopened.",
                $ticket,
                'reopened',
                $user
            ));
        }

        if ($ticket->agent && (int) $ticket->agent_id !== (int) $user->id) {
            $ticket->agent->notify(new TicketEventNotification(
                'Assigned ticket reopened',
                "Ticket {$ticket->ticket_number} has been reopened.",
                $ticket,
                'reopened',
                $user
            ));
        }

        return back()->with('success', 'Ticket reopened successfully.');
    }

    public function close(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);
        abort_unless($user->can('update', $ticket), 403);

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

        $ticket->loadMissing(['user', 'agent']);

        if ((int) $ticket->user_id !== (int) $user->id) {
            $ticket->user?->notify(new TicketEventNotification(
                'Ticket closed',
                "Ticket {$ticket->ticket_number} has been closed.",
                $ticket,
                'closed',
                $user
            ));
        }

        return back()->with('success', 'Ticket closed successfully.');
    }
    public function destroy(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);

        abort_unless($user->roleName() === 'admin', 403);

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

        abort_unless($user->roleName() === 'admin', 403);

        $ticket = Ticket::onlyTrashed()->findOrFail($id);
        $ticket->restore();

        return redirect()
            ->route('tickets.trashed')
            ->with('success', 'Ticket restored successfully.');
    }

    public function forceDelete(Request $request, int|string $id)
    {
        $user = $this->currentUser($request);

        abort_unless($user->roleName() === 'admin', 403);

        $ticket = Ticket::onlyTrashed()->findOrFail($id);
        $paths = $ticket->attachments()->pluck('file_path')->all();
        $ticket->forceDelete();

        Storage::disk(TicketAttachment::DISK)->delete($paths);

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
