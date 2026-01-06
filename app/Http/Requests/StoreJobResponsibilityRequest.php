<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobResponsibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_id' => 'required|exists:positions,id',
            'task_name' => 'required|string|max:200',
            'task_description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'estimated_hours' => 'nullable|integer|min:0|max:100',
            'frequency' => 'required|in:daily,weekly,monthly,yearly,as_needed',
            'is_core_responsibility' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'position_id.exists' => 'Jabatan tidak ditemukan',
            'priority.in' => 'Prioritas harus low, medium, high, atau critical',
            'frequency.in' => 'Frekuensi harus daily, weekly, monthly, yearly, atau as_needed',
        ];
    }
}
