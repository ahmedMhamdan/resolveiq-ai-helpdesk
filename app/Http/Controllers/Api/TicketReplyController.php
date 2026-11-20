<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketReplyController extends Controller
{
    public function index(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $this->currentUser($request);
        if (! $user->can('view', $ticket)) {
            return response()->json([
                'message' => 'You are not allowed to view replies for this ticket.',
            ], 403);
        }

        $replies = $ticket->replies()
            ->with('user.role')
            ->visibleTo($user)
            ->oldest()
            ->get()
            ->map(fn (TicketReply $reply) => $this->formatReply($reply))
            ->values();

        return response()->json([
            'message' => 'Replies retrieved successfully.',
            'data' => $replies,
        ]);
    }

    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $this->currentUser($request);
        $role = $user->roleName();

        if (! $user->can('view', $ticket)) {
            return response()->json([
                'message' => 'You are not allowed to reply to this ticket.',
            ], 403);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_internal_note') && ! in_array($role, ['admin', 'agent'], true)) {
            return response()->json([
                'message' => 'Only admins and agents can create internal notes.',
            ], 403);
        }

        $isInternalNote = $request->boolean('is_internal_note');

        $reply = DB::transaction(function () use ($ticket, $user, $data, $isInternalNote) {
            $reply = $ticket->replies()->create([
                'user_id' => $user->id,
                'message' => $data['message'],
                'is_internal_note' => $isInternalNote,
            ]);

            if (! $isInternalNote && $user->isSupportStaff()) {
                $ticket->recordFirstResponse();
            }

            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => $isInternalNote ? 'Internal note added via API' : 'Reply added via API',
                'new_value' => $isInternalNote ? 'internal_note' : 'reply',
            ]);

            return $reply;
        });


        $reply->load('user.role');
        $ticket->loadMissing(['user', 'agent']);

        $this->sendReplyNotification($ticket, $reply, $user, $role, $isInternalNote);

        return response()->json([
            'message' => 'Reply added successfully.',
            'reply' => $this->formatReply($reply),
        ], 201);
    }

    private function sendReplyNotification(
        Ticket $ticket,
        TicketReply $reply,
        User $user,
        string $role,
        bool $isInternalNote
    ): void {
        if ($isInternalNote) {
            return;
        }

        $replyPreview = Str::limit(trim($reply->message), 120);

        if (in_array($role, ['admin', 'agent'], true) && (int) $ticket->user_id !== (int) $user->id) {
            $ticket->user?->notify(new TicketEventNotification(
                "Ticket {$ticket->ticket_number} updated",
                "{$user->name} replied to this ticket.\n\"{$replyPreview}\"",
                $ticket,
                'reply',
                $user
            ));
        }

        if ($role === 'user' && $ticket->agent && (int) $ticket->agent_id !== (int) $user->id) {
            $ticket->agent->notify(new TicketEventNotification(
                "Ticket {$ticket->ticket_number} updated",
                "{$user->name} replied to this ticket.\n\"{$replyPreview}\"",
                $ticket,
                'reply',
                $user
            ));
        }
    }

    private function currentUser(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function formatReply(TicketReply $reply): array
    {
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
    }
}
