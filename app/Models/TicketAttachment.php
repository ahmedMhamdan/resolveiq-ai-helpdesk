<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'ticket_reply_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function reply()
    {
        return $this->belongsTo(TicketReply::class, 'ticket_reply_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
