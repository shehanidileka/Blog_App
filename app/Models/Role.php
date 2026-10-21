<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [];

    protected $casts = ['is_system' => 'boolean'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    // users.role column stores the role slug
    public function users()
    {
        return $this->hasMany(User::class, 'role', 'slug');
    }
}