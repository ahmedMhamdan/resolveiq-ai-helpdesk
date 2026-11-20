<?php

namespace App\Models;

use App\Notifications\ResolveIqResetPasswordNotification;
use App\Notifications\ResolveIqVerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role_id', 'avatar_path', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function roleName(): string
    {
        return match (strtolower($this->role?->name ?? '')) {
            'admin' => 'admin',
            'agent' => 'agent',
            default => 'user',
        };
    }

    public function isSupportStaff(): bool
    {
        return $this->roleName() !== 'user';
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'agent_id');
    }

    public function ticketReplies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function ticketAttachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function ticketActivityLogs()
    {
        return $this->hasMany(TicketActivityLog::class);
    }

    public function avatarUrl(): string
    {
        if (! $this->avatar_path) {
            return '';
        }

        $path = ltrim($this->avatar_path, '/');

        if (str_starts_with($path, 'https://') || str_starts_with($path, 'http://')) {
            return $path;
        }

        return asset(str_starts_with($path, 'images/') || str_starts_with($path, 'storage/')
            ? $path
            : 'storage/'.$path);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResolveIqResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new ResolveIqVerifyEmailNotification());
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
