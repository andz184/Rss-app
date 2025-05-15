<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'feed_url',
        'site_url',
        'description',
        'language',
        'icon',
        'last_updated',
        'is_active',
        'error_count',
        'category_id',
        'user_id',
        'etag',
        'last_modified',
        'css_selector',
        'content_type',
        'is_scraped',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
        'is_active' => 'boolean',
        'is_scraped' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
