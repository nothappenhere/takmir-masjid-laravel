<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'order',
        'status',
        'level',
    ];

    protected $casts = [
        'status' => 'string',
        'level' => 'string',
        'order' => 'integer',
    ];

    /**
     * Get the committees that hold this position.
     */
    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class);
    }

    /**
     * Get the position histories for this position.
     */
    public function positionHistories(): HasMany
    {
        return $this->hasMany(PositionHistory::class);
    }

    /**
     * Get the job responsibilities for this position.
     */
    public function responsibilities(): HasMany
    {
        return $this->hasMany(JobResponsibility::class);
    }

    /**
     * Get the parent position.
     */
    public function parent()
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }

    /**
     * Get the child positions.
     */
    public function children()
    {
        return $this->hasMany(Position::class, 'parent_id');
    }

    /**
     * Get the organizational structure entry.
     */
    public function organizationalStructure()
    {
        return $this->hasOne(OrganizationalStructure::class);
    }

    /**
     * Scope active positions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope leadership positions.
     */
    public function scopeLeadership($query)
    {
        return $query->where('level', 'leadership');
    }
}
