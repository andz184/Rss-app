<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'model_provider',
        'model_name',
        'endpoint_url',
        'api_key_encrypted',
        'grounding_model_provider',
        'grounding_model_name',
        'grounding_resize_width',
        'grounding_resize_height',
        'platform',
        'observation_type',
        'search_engine',
        'is_active',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'grounding_resize_width' => 'integer',
        'grounding_resize_height' => 'integer',
    ];

    /**
     * Get the user that owns the agent.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tasks for the agent.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(AgentTask::class);
    }

    /**
     * Get the logs for the agent.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AgentLog::class);
    }

    /**
     * Get the knowledge base entries for the agent.
     */
    public function knowledgeBase(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class);
    }
}
