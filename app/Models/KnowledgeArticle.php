<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeArticle extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
