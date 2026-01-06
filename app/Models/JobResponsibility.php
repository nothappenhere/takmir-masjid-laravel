<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobResponsibility extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'position_id',
        'task_name',
        'task_description',
        'priority',
        'estimated_hours',
        'frequency',
        'is_core_responsibility',
    ];

    protected $casts = [
        'priority' => 'string',
        'estimated_hours' => 'integer',
        'frequency' => 'string',
        'is_core_responsibility' => 'boolean',
    ];

    /**
     * Get the position that this responsibility belongs to.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the task assignments for this responsibility.
     */
    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    /**
     * Scope core responsibilities.
     */
    public function scopeCore($query)
    {
        return $query->where('is_core_responsibility', true);
    }

    /**
     * Scope by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get active assignments count.
     */
    public function getActiveAssignmentsCountAttribute(): int
    {
        return $this->taskAssignments()
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
    }
}
