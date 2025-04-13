<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'image_path',
        'caption',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_liked', 'image_url'];

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Check if the post is liked by the current authenticated user.
     *
     * @return bool
     */
    public function isLiked(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->likes()->where('user_id', auth()->id())->exists();
    }

    /**
     * Get the is_liked attribute for JSON serialization.
     *
     * @return bool
     */
    public function getIsLikedAttribute(): bool
    {
        return $this->isLiked();
    }

    /**
     * Get the image_url attribute for JSON serialization.
     *
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        
        // Check if image_path already contains http/https protocol
        if (preg_match('/^https?:\/\//', $this->image_path)) {
            return $this->image_path;
        }
        
        return url('/img/post/' . basename($this->image_path));
    }
}
