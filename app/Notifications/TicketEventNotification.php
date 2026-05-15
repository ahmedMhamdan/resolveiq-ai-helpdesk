<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $title,
        private string $message,
        private Ticket $ticket,
        private string $type = 'ticket',
        private ?User $actor = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'ticket_title' => $this->ticket->title,
            'actor_id' => $this->actor?->id,
            'actor_name' => $this->actor?->name ?? 'System',
            'url' => route('tickets.show', $this->ticket),
        ];
    }
}
