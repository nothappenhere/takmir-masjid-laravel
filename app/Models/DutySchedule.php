<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DutySchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'committee_id',
        'duty_date',
        'start_time',
        'end_time',
        'location',
        'duty_type',
        'status',
        'remarks',
        'is_recurring',
        'recurring_type',
        'recurring_end_date',
    ];

    protected $casts = [
        'duty_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'recurring_end_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the committee assigned to this duty.
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Scope today's duties.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('duty_date', today());
    }

    /**
     * Scope upcoming duties.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereDate('duty_date', '>=', today())
            ->whereDate('duty_date', '<=', today()->addDays($days));
    }

    /**
     * Scope by duty type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('duty_type', $type);
    }

    /**
     * Check if duty is ongoing.
     */
    public function getIsOngoingAttribute(): bool
    {
        $now = now();

        $dutyDate = Carbon::parse($this->duty_date);
        $dutyDateTime = $dutyDate->format('Y-m-d');

        $start = Carbon::parse($dutyDateTime . ' ' . $this->start_time->format('H:i:s'));
        $end = Carbon::parse($dutyDateTime . ' ' . $this->end_time->format('H:i:s'));

        return $now->between($start, $end) && $this->status === 'ongoing';
    }
}
