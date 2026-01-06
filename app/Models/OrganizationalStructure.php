<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationalStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'position_id',
        'parent_id',
        'lft',
        'rgt',
        'depth',
        'order',
        'is_division',
        'division_name',
        'division_description',
    ];

    protected $casts = [
        'lft' => 'integer',
        'rgt' => 'integer',
        'depth' => 'integer',
        'order' => 'integer',
        'is_division' => 'boolean',
    ];

    /**
     * Get the position associated with this structure entry.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the parent structure entry.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationalStructure::class, 'parent_id');
    }

    /**
     * Get the child structure entries.
     */
    public function children(): HasMany
    {
        return $this->hasMany(OrganizationalStructure::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get the committees in this position.
     */
    public function committees()
    {
        return $this->hasManyThrough(
            Committee::class,
            Position::class,
            'id', // Foreign key on positions table
            'position_id', // Foreign key on committees table
            'position_id', // Local key on organizational_structures table
            'id' // Local key on positions table
        )->active();
    }

    /**
     * Scope root level structure entries.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id')->orderBy('order');
    }

    /**
     * Scope divisions.
     */
    public function scopeDivisions($query)
    {
        return $query->where('is_division', true);
    }

    /**
     * Get full hierarchical path.
     */
    public function getPathAttribute(): string
    {
        $path = [];
        $current = $this;

        while ($current) {
            $name = $current->is_division ?
                $current->division_name : ($current->position ? $current->position->name : 'N/A');
            array_unshift($path, $name);
            $current = $current->parent;
        }

        return implode(' → ', $path);
    }
}
