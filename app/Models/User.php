<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'google_id', 'avatar', 'bio', 'website', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected ?array $permissionCache = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Always returns a usable URL (Google URL or an uploaded file) or null
    protected function avatar(): Attribute
    {
        return Attribute::get(function ($value) {
            if (! $value) {
                return null;
            }

            return Str::startsWith($value, ['http://', 'https://'])
                ? $value
                : asset('storage/' . $value);
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function permissionSlugs(): array
    {
        if ($this->permissionCache === null) {
            $this->permissionCache = $this->roleModel
                ? $this->roleModel->permissions()->pluck('permissions.slug')->all()
                : [];
        }

        return $this->permissionCache;
    }

    public function hasPermission(string $slug): bool
    {
        return $this->isAdmin() || in_array($slug, $this->permissionSlugs(), true);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}