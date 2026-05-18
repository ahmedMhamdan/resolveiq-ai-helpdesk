<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $this->roleName($user);

        $tickets = $this->ticketQueryFor($role, $user)
            ->with(['user', 'agent', 'department'])
            ->when($request->query('search'), function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($request->query('status'), function (Builder $query, string $status) {
                $query->where('status', $status);
            })
            ->when($request->query('priority'), function (Builder $query, string $priority) {
                $query->where('priority', $priority);
            })
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Tickets retrieved successfully.',
            'data' => $tickets->getCollection()
                ->map(fn (Ticket $ticket) => $this->formatTicket($ticket))
                ->values(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'department_id' => ['required', 'exists:departments,id'],
        ]);

        $ticket = Ticket::create([
            'ticket_number' => $this->makeTicketNumber(),
            'user_id' => $user->id,
            'agent_id' => null,
            'department_id' => $data['department_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => 'open',
            'priority' => null,
            'due_at' => null,
        ]);

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => 'Ticket created via API',
            'old_value' => null,
            'new_value' => 'open',
        ]);

        $ticket->load(['user', 'agent', 'department']);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'ticket' => $this->formatTicket($ticket),
        ], 201);
    }

    public function show(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if (! $this->canViewTicket($user, $ticket)) {
            return response()->json([
                'message' => 'You are not allowed to view this ticket.',
            ], 403);
        }

        $ticket->load([
            'user',
            'agent',
            'department',
            'replies.user.role',
            'activityLogs.user',
        ]);

        return response()->json([
            'ticket' => $this->formatTicket($ticket, true),
        ]);
    }

    private function ticketQueryFor(string $role, User $user): Builder
    {
        return Ticket::query()
            ->when($role === 'user', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($role === 'agent', function (Builder $query) use ($user) {
                $query->where('agent_id', $user->id);
            });
    }

    private function canViewTicket(User $user, Ticket $ticket): bool
    {
        $role = $this->roleName($user);

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'agent') {
            return (int) $ticket->agent_id === (int) $user->id;
        }

        return (int) $ticket->user_id === (int) $user->id;
    }

    private function roleName(User $user): string
    {
        return strtolower($user->role?->name ?? 'user');
    }

    private function makeTicketNumber(): string
    {
        do {
            $number = 'RIQ-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (Ticket::query()->where(['ticket_number' => $number])->exists());

        return $number;
    }

    private function formatTicket(Ticket $ticket, bool $includeDetails = false): array
    {
        $data = [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'due_at' => $ticket->due_at?->toDateTimeString(),
            'created_at' => $ticket->created_at?->toDateTimeString(),
            'requester' => [
                'id' => $ticket->user?->id,
                'name' => $ticket->user?->name,
                'email' => $ticket->user?->email,
            ],
            'agent' => $ticket->agent ? [
                'id' => $ticket->agent->id,
                'name' => $ticket->agent->name,
                'email' => $ticket->agent->email,
            ] : null,
            'department' => $ticket->department ? [
                'id' => $ticket->department->id,
                'name' => $ticket->department->name,
            ] : null,
        ];

        if ($includeDetails) {
            $data['replies'] = $ticket->replies->map(function ($reply) {
                return [
                    'id' => $reply->id,
                    'message' => $reply->message,
                    'is_internal_note' => (bool) $reply->is_internal_note,
                    'created_at' => $reply->created_at?->toDateTimeString(),
                    'user' => [
                        'id' => $reply->user?->id,
                        'name' => $reply->user?->name,
                        'role' => $reply->user?->role?->name,
                    ],
                ];
            })->values();

            $data['activity_logs'] = $ticket->activityLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'created_at' => $log->created_at?->toDateTimeString(),
                    'user' => [
                        'id' => $log->user?->id,
                        'name' => $log->user?->name,
                    ],
                ];
            })->values();
        }

        return $data;
    }
}
