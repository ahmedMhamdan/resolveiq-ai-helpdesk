<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'agent_id',
        'department_id',
        'title',
        'description',
        'status',
        'priority',
        'due_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        match ($user->roleName()) {
            'admin' => null,
            'agent' => $query->where('agent_id', $user->id),
            default => $query->where('user_id', $user->id),
        };
    }

    #[Scope]
    protected function matchingSearch(Builder $query, ?string $search): void
    {
        if (filled($search)) {
            $query->where(function (Builder $query) use ($search) {
                $query->whereLike('ticket_number', "%{$search}%")
                    ->orWhereLike('title', "%{$search}%");
            });
        }
    }

    public function recordFirstResponse(): void
    {
        static::query()->whereKey($this->id)->whereNull('first_response_at')
            ->update([
                'first_response_at' => now(),
                'status' => DB::raw("CASE WHEN status = 'open' THEN 'pending' ELSE status END"),
            ]);
    }

    public static function createWithNumber(callable $generateNumber, array $attributes, string $action): self
    {
        return retry(5, fn () => DB::transaction(function () use ($generateNumber, $attributes, $action) {
            $ticket = static::create(['ticket_number' => $generateNumber()] + $attributes);

            $ticket->activityLogs()->create([
                'user_id' => $ticket->user_id,
                'action' => $action,
                'new_value' => $ticket->status,
            ]);

            return $ticket;
        }), when: fn ($exception) => $exception instanceof UniqueConstraintViolationException);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(TicketActivityLog::class);
    }
}
