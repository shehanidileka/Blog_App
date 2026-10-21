<?php

namespace App\Models;

use App\Support\MetaImage;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\Html;

class Post extends Model
{
    protected $guarded = [];
        protected $casts = [
        'allow_comments' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            $categoryName = $post->category_id
                ? Category::whereKey($post->category_id)->value('name')
                : null;

            // NEW: use what the author wrote in the form, fill the empty ones automatically
            $post->meta_title = filled($post->meta_title)
                ? Str::limit(trim($post->meta_title), 70, '')
                : Seo::title($post->title);

            $post->meta_description = filled($post->meta_description)
                ? Str::limit(trim($post->meta_description), 170, '')
                : Seo::description($post->excerpt, $post->body);

            $post->meta_keywords = filled($post->meta_keywords)
                ? Str::limit(trim($post->meta_keywords), 255, '')
                : Seo::keywords($post->title, $post->body, $categoryName);

            // Meta image: a 1200x630 JPEG made from the cover image
            if ($post->image) {
                $needsNew = $post->isDirty('image')
                    || ! $post->meta_image
                    || $post->meta_image === $post->image
                    || ! Storage::disk('public')->exists($post->meta_image);

                if ($needsNew) {
                    // remove the old generated image (never the cover itself)
                    $old = $post->getOriginal('meta_image');
                    if ($old && $old !== $post->getOriginal('image') && $old !== $post->image) {
                        Storage::disk('public')->delete($old);
                    }

                    $post->meta_image = MetaImage::fromCover($post->image) ?: $post->image;
                }
            } else {
                $post->meta_image = null;
            }
        });

        static::deleting(function (Post $post) {
            if ($post->meta_image && $post->meta_image !== $post->image) {
                Storage::disk('public')->delete($post->meta_image);
            }
        });
    }

        // ---- Approval ----

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

        public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    // Safe HTML for the post page (old plain-text posts are converted too)
    public function getBodyHtmlAttribute(): string
    {
        return Html::render($this->body);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // ---- SEO helpers used in the post page <head> ----

    public function getSeoTitleAttribute()
    {
        return $this->meta_title ?: $this->title;
    }

    public function getSeoDescriptionAttribute()
    {
        return $this->meta_description
            ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($this->excerpt ?: $this->body))), 160);
    }

    public function getSeoImageAttribute()
    {
        if ($this->meta_image) {
            return asset('storage/' . $this->meta_image);
        }

        return $this->image_url;
    }
}