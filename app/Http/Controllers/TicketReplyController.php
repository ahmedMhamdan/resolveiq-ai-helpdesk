<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\TicketAttachment;
use App\Notifications\TicketEventNotification;

class TicketReplyController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $user = $this->currentUser($request);
        $role = $this->roleName($user);

        abort_unless($this->canViewTicket($user, $ticket), 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
            'attachments.*' => ['nullable', 'file', 'max:5120'],
        ]);

        $isInternalNote = $request->boolean('is_internal_note') && in_array($role, ['admin', 'agent'], true);

        $reply = $ticket->replies()->create([
            'user_id' => $user->id,
            'message' => $data['message'],
            'is_internal_note' => $isInternalNote,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');

                $ticket->attachments()->create([
                    'ticket_reply_id' => $reply->id,
                    'user_id' => $user->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        if (! $isInternalNote && in_array($role, ['admin', 'agent'], true) && is_null($ticket->first_response_at)) {
            $ticket->update([
                'first_response_at' => now(),
                'status' => $ticket->status === 'open' ? 'pending' : $ticket->status,
            ]);
        }

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => $isInternalNote ? 'Internal note added' : 'Reply added',
            'old_value' => null,
            'new_value' => $isInternalNote ? 'internal_note' : 'reply',
        ]);
        $ticket->loadMissing(['user', 'agent']);

        if (! $isInternalNote) {
            if (in_array($role, ['admin', 'agent'], true) && (int) $ticket->user_id !== (int) $user->id) {
                $ticket->user?->notify(new TicketEventNotification(
                    'New reply on your ticket',
                    "{$user->name} replied to ticket {$ticket->ticket_number}.",
                    $ticket,
                    'reply',
                    $user
                ));
            }

            if ($role === 'user' && $ticket->agent && (int) $ticket->agent_id !== (int) $user->id) {
                $ticket->agent->notify(new TicketEventNotification(
                    'Customer replied',
                    "{$user->name} replied to ticket {$ticket->ticket_number}.",
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

        abort_unless($this->roleName($user) === 'admin', 403);

        if ((int) $reply->ticket_id !== (int) $ticket->id) {
            abort(404);
        }

        foreach ($reply->attachments as $attachment) {
            if ($attachment->file_path) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            TicketAttachment::query()
            ->whereKey($attachment->getKey())
            ->delete();
        }

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => $reply->is_internal_note ? 'Internal note deleted' : 'Reply deleted',
            'old_value' => $reply->message,
            'new_value' => null,
        ]);

        TicketReply::query()
            ->whereKey($reply->getKey())
            ->delete();

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

    private function roleName(User $user): string
    {
        return strtolower($user->role?->name ?? 'user');
    }

    private function canViewTicket(User $user, Ticket $ticket): bool
    {
        $role = $this->roleName($user);

        return $role === 'admin'
            || ($role === 'agent' && $ticket->agent_id === $user->id)
            || ($role === 'user' && $ticket->user_id === $user->id);
    }
}
