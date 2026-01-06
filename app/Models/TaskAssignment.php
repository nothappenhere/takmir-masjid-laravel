<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // TAMBAHKAN INI

class TaskAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'committee_id',
        'job_responsibility_id',
        'assigned_date',
        'due_date',
        'status',
        'progress_percentage',
        'notes',
        'completed_date',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
        'completed_date' => 'date',
        'approved_at' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    /**
     * Get the committee assigned to this task.
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Get the job responsibility for this task.
     */
    public function jobResponsibility(): BelongsTo
    {
        return $this->belongsTo(JobResponsibility::class);
    }

    /**
     * Get the approver committee.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'approved_by');
    }

    /**
     * Scope pending tasks.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('due_date', '<', today());
    }

    /**
     * Scope tasks due soon (within 3 days).
     */
    public function scopeDueSoon($query)
    {
        return $query->where('status', '!=', 'completed')
            ->whereBetween('due_date', [today(), today()->addDays(3)]);
    }

    /**
     * Check if task is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        // PERBAIKAN: Gunakan Carbon instance untuk lt()
        $dueDate = Carbon::parse($this->due_date);

        return $dueDate->lt(today()) &&
            !in_array($this->status, ['completed', 'cancelled']);
    }
}
