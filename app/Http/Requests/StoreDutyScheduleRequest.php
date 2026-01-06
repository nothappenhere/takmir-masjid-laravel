<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDutyScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'committee_id' => 'required|exists:committees,id',
            'duty_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:200',
            'duty_type' => ['required', Rule::in(['piket', 'kebersihan', 'keamanan', 'administrasi', 'lainnya'])],
            'status' => ['nullable', Rule::in(['pending', 'ongoing', 'completed', 'cancelled'])],
            'remarks' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly',
            'recurring_end_date' => 'nullable|date|after:duty_date',
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai',
            'committee_id.exists' => 'Pengurus tidak ditemukan',
        ];
    }
}
