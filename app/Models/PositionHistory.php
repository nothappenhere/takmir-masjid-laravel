<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PositionHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'committee_id',
        'position_id',
        'start_date',
        'end_date',
        'is_active',
        'appointment_type',
        'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
        'appointment_type' => 'string',
    ];

    /**
     * Get the committee that holds this position history.
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Get the position associated with this history.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Scope active position histories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by appointment type.
     */
    public function scopeByAppointmentType($query, $type)
    {
        return $query->where('appointment_type', $type);
    }

    /**
     * Get duration in months.
     */
    public function getDurationInMonthsAttribute(): ?int
    {
        if (!$this->start_date) {
            return null;
        }

        $startDate = Carbon::parse($this->start_date);
        $endDate = $this->end_date ? Carbon::parse($this->end_date) : now();

        return $startDate->diffInMonths($endDate);
    }
}
