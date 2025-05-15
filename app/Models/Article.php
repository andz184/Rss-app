<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'guid',
        'title',
        'author',
        'content',
        'url',
        'comments_url',
        'date',
        'is_read',
        'is_favorite',
        'feed_id',
        'image',
        'hash',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_read' => 'boolean',
        'is_favorite' => 'boolean',
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }
}
