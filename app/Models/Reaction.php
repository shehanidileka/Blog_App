<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    protected $guarded = [];

    public const TYPES = [
        'like'  => '👍',
        'love'  => '❤️',
        'haha'  => '😂',
        'wow'   => '😮',
        'sad'   => '😢',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}