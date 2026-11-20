<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
    ];

    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if (! $user->isSupportStaff()) {
            $query->whereNotIn('action', [
                'Internal note added',
                'Internal note added via API',
                'Internal note deleted',
                'AI internal note added',
                'Attachment deleted',
            ]);
        }
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
