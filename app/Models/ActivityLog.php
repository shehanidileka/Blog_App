<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $guarded = [];

    protected $casts = ['properties' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // "post.created" -> "post"
    public function getGroupAttribute(): string
    {
        return explode('.', $this->action)[0];
    }

    // Bootstrap colour for the badge
    public function getToneAttribute(): string
    {
        return match (true) {
            str_ends_with($this->action, 'deleted'), str_ends_with($this->action, 'login_failed'), str_ends_with($this->action, 'rejected') => 'danger',
            str_ends_with($this->action, 'created'), str_ends_with($this->action, 'approved'), str_ends_with($this->action, 'login') => 'success',
            str_ends_with($this->action, 'updated'), str_ends_with($this->action, 'permissions_updated'), str_ends_with($this->action, 'role_changed') => 'warning',
            default => 'secondary',
        };
    }
}