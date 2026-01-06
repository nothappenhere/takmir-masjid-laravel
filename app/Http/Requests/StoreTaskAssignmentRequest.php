<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'committee_id' => 'required|exists:committees,id',
            'job_responsibility_id' => 'required|exists:job_responsibilities,id',
            'assigned_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:assigned_date',
            'status' => 'required|in:pending,in_progress,completed,overdue,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'completed_date' => 'nullable|date|after_or_equal:assigned_date',
            'approved_by' => 'nullable|exists:committees,id',
        ];
    }

    public function messages(): array
    {
        return [
            'committee_id.exists' => 'Pengurus tidak ditemukan',
            'job_responsibility_id.exists' => 'Tugas tidak ditemukan',
            'due_date.after_or_equal' => 'Tanggal jatuh tempo harus setelah atau sama dengan tanggal penugasan',
            'progress_percentage.min' => 'Persentase progres minimal 0%',
            'progress_percentage.max' => 'Persentase progres maksimal 100%',
        ];
    }
}
