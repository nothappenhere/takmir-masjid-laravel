<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDutyScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'committee_id' => 'sometimes|required|exists:committees,id',
            'duty_date' => 'sometimes|required|date',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'location' => 'sometimes|required|string|max:200',
            'duty_type' => ['sometimes', 'required', Rule::in(['piket', 'kebersihan', 'keamanan', 'administrasi', 'lainnya'])],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'ongoing', 'completed', 'cancelled'])],
            'remarks' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly',
            'recurring_end_date' => 'nullable|date|after:duty_date',
        ];
    }
}
