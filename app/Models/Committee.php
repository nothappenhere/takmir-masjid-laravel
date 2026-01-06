<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Committee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'committees';

    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'gender',
        'address',
        'birth_date',
        'join_date',
        'active_status',
        'position_id',
        'user_id',
        'photo_path',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date'  => 'date',
        'active_status' => 'string',
        'gender' => 'string',
    ];

    /**
     * Get the current position of the committee.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the user account associated with the committee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the position histories for the committee.
     */
    public function positionHistories(): HasMany
    {
        return $this->hasMany(PositionHistory::class);
    }

    /**
     * Get the duty schedules for the committee.
     */
    public function dutySchedules(): HasMany
    {
        return $this->hasMany(DutySchedule::class);
    }

    /**
     * Get the task assignments for the committee.
     */
    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    /**
     * Get current active position history.
     */
    public function currentPositionHistory()
    {
        return $this->hasOne(PositionHistory::class)->where('is_active', true)->latest();
    }

    /**
     * Scope active committees.
     */
    public function scopeActive($query)
    {
        return $query->where('active_status', 'active');
    }

    /**
     * Scope by position.
     */
    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    /**
     * Get age of committee member.
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        $birthDate = Carbon::parse($this->birth_date);
        return now()->diffInYears($birthDate);
    }

    /**
     * Get tenure in years.
     */
    public function getTenureAttribute(): ?int
    {
        if (!$this->join_date) {
            return null;
        }

        $joinDate = Carbon::parse($this->join_date);
        return now()->diffInYears($joinDate);
    }
}
