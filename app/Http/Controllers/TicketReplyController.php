<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\TicketAttachment;
use App\Notifications\TicketEventNotification;
use RuntimeException;
use Throwable;

class TicketReplyController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);
        $role = $user->roleName();

        abort_unless($user->can('view', $ticket), 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
            'attachments' => ['sometimes', 'array', 'max:10'],
            'attachments.*' => ['required', 'file', 'max:5120'],
        ]);

        $isInternalNote = $request->boolean('is_internal_note') && in_array($role, ['admin', 'agent'], true);

        $paths = [];

        try {
            $reply = DB::transaction(function () use ($ticket, $user, $data, $isInternalNote, &$paths) {
                $reply = $ticket->replies()->create([
                    'user_id' => $user->id,
                    'message' => $data['message'],
                    'is_internal_note' => $isInternalNote,
                ]);

                foreach ($data['attachments'] ?? [] as $file) {
                    $path = $file->store('ticket-attachments', TicketAttachment::DISK);

                    if ($path === false) {
                        throw new RuntimeException('Failed to store ticket attachment.');
                    }

                    $paths[] = $path;

                    $ticket->attachments()->create([
                        'ticket_reply_id' => $reply->id,
                        'user_id' => $user->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }

                if (! $isInternalNote && $user->isSupportStaff()) {
                    $ticket->recordFirstResponse();
                }

                $ticket->activityLogs()->create([
                    'user_id' => $user->id,
                    'action' => $isInternalNote ? 'Internal note added' : 'Reply added',
                    'new_value' => $isInternalNote ? 'internal_note' : 'reply',
                ]);

                return $reply;
            });
        } catch (Throwable $exception) {
            Storage::disk(TicketAttachment::DISK)->delete($paths);

            throw $exception;
        }

        $replyPreview = Str::limit(trim($reply->message), 120);
        $ticket->loadMissing(['user', 'agent']);

        if (! $isInternalNote) {
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

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Reply added successfully.');
    }

    public function destroy(Request $request, Ticket $ticket, TicketReply $reply)
    {
        $user = $request->user();

        abort_unless($user->roleName() === 'admin', 403);

        if ((int) $reply->ticket_id !== (int) $ticket->id) {
            abort(404);
        }

        $paths = $reply->attachments()->pluck('file_path')->all();

        DB::transaction(function () use ($ticket, $user, $reply) {
            $ticket->activityLogs()->create([
                'user_id' => $user->id,
                'action' => $reply->is_internal_note ? 'Internal note deleted' : 'Reply deleted',
                'old_value' => $reply->is_internal_note ? $reply->message : null,
            ]);

            $reply->attachments()->delete();
            $reply->delete();
        });

        Storage::disk(TicketAttachment::DISK)->delete($paths);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Reply deleted successfully.');
    }

    private function currentUser(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

}
