<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::with(['user', 'agent', 'department'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->priority, fn ($query, $priority) => $query->where('priority', $priority))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
        'user',
        'agent',
        'department',
        'replies.user',
        'replies.attachments',
        'activityLogs.user',
    ]);

        return view('tickets.show', compact('ticket'));
    }
    public function create()
    {
        $departments = Department::orderBy('name', 'asc')->get();

        $agents = User::whereHas('role', function ($query) {
        $query->where('name', 'agent');
        })->orderBy('name', 'asc')->get();

        return view('tickets.create', compact('departments', 'agents'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'department_id' => ['required', 'exists:departments,id'],
            'agent_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_at' => ['nullable', 'date'],
        ]);

        $requesterId = User::whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->value('id');

        $actorId = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->value('id');

        $latestTicket = Ticket::latest('id')->first();
        $nextNumber = $latestTicket ? $latestTicket->id + 1001 : 1001;

        $ticket = Ticket::create([
            'ticket_number' => 'RIQ-' . $nextNumber,
            'user_id' => $requesterId,
            'agent_id' => $data['agent_id'] ?? null,
            'department_id' => $data['department_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => 'open',
            'priority' => $data['priority'],
            'due_at' => $data['due_at'] ?? null,
        ]);

        $ticket->activityLogs()->create([
            'user_id' => $actorId,
            'action' => 'Ticket created',
            'old_value' => null,
            'new_value' => 'open',
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }
    public function edit(Ticket $ticket)
    {
        $departments = Department::orderBy('name', 'asc')->get();

        $agents = User::whereHas('role', function ($query) {
            $query->where('name', 'agent');
        })->orderBy('name', 'asc')->get();

        return view('tickets.edit', compact('ticket', 'departments', 'agents'));
    }

public function update(Request $request, Ticket $ticket)
{
    $data = $request->validate([
        'title' => ['required', 'string', 'max:180'],
        'description' => ['required', 'string', 'max:5000'],
        'department_id' => ['required', 'exists:departments,id'],
        'agent_id' => ['nullable', 'exists:users,id'],
        'status' => ['required', 'in:open,pending,solved,closed'],
        'priority' => ['required', 'in:low,medium,high,urgent'],
        'due_at' => ['nullable', 'date'],
    ]);

    $oldStatus = $ticket->status;
    $oldPriority = $ticket->priority;

    $resolvedAt = $ticket->resolved_at;
    $closedAt = $ticket->closed_at;

    if ($data['status'] === 'solved' && $ticket->status !== 'solved') {
        $resolvedAt = now();
    }

    if ($data['status'] === 'closed' && $ticket->status !== 'closed') {
        $closedAt = now();
    }

    if (! in_array($data['status'], ['solved', 'closed'])) {
        $resolvedAt = null;
        $closedAt = null;
    }

    $ticket->update([
        'title' => $data['title'],
        'description' => $data['description'],
        'department_id' => $data['department_id'],
        'agent_id' => $data['agent_id'] ?? null,
        'status' => $data['status'],
        'priority' => $data['priority'],
        'due_at' => $data['due_at'] ?? null,
        'resolved_at' => $resolvedAt,
        'closed_at' => $closedAt,
    ]);

    $actorId = User::whereHas('role', function ($query) {
        $query->where('name', 'admin');
    })->value('id');

    if ($oldStatus !== $ticket->status) {
        $ticket->activityLogs()->create([
            'user_id' => $actorId,
            'action' => 'Status changed',
            'old_value' => $oldStatus,
            'new_value' => $ticket->status,
        ]);
    }

    if ($oldPriority !== $ticket->priority) {
        $ticket->activityLogs()->create([
            'user_id' => $actorId,
            'action' => 'Priority changed',
            'old_value' => $oldPriority,
            'new_value' => $ticket->priority,
        ]);
    }

    if ($oldStatus === $ticket->status && $oldPriority === $ticket->priority) {
        $ticket->activityLogs()->create([
            'user_id' => $actorId,
            'action' => 'Ticket updated',
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    return redirect()
        ->route('tickets.show', $ticket)
        ->with('success', 'Ticket updated successfully.');
}
    public function deleted()
    {
        $tickets = Ticket::onlyTrashed()
            ->with(['user', 'agent', 'department'])
            ->latest('deleted_at')
            ->paginate(10);

        return view('tickets.deleted', compact('tickets'));
    }

    public function destroy(Ticket $ticket)
    {
        Ticket::query()
            ->whereKey($ticket->id)
            ->delete();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Ticket moved to deleted tickets.');
    }

    public function restore($id)
    {
        $ticket = Ticket::onlyTrashed()->findOrFail($id);
        $ticket->restore();

        return redirect()
            ->route('tickets.deleted')
            ->with('success', 'Ticket restored successfully.');
    }

    public function forceDelete($id)
    {
        $ticket = Ticket::onlyTrashed()->findOrFail($id);
        $ticket->forceDelete();

        return redirect()
            ->route('tickets.deleted')
            ->with('success', 'Ticket permanently deleted.');
    }
}
