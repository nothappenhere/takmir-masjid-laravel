<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'committee_id' => 'sometimes|required|exists:committees,id',
            'job_responsibility_id' => 'sometimes|required|exists:job_responsibilities,id',
            'assigned_date' => 'sometimes|required|date',
            'due_date' => 'nullable|date|after_or_equal:assigned_date',
            'status' => 'sometimes|required|in:pending,in_progress,completed,overdue,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'completed_date' => 'nullable|date|after_or_equal:assigned_date',
            'approved_by' => 'nullable|exists:committees,id',
        ];
    }
}
