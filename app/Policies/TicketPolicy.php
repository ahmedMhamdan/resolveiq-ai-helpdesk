<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return match ($user->roleName()) {
            'admin' => true,
            'agent' => (int) $ticket->agent_id === (int) $user->id,
            default => (int) $ticket->user_id === (int) $user->id,
        };
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isSupportStaff() && $this->view($user, $ticket);
    }
}
