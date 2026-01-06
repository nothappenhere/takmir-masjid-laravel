<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobResponsibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_id' => 'sometimes|required|exists:positions,id',
            'task_name' => 'sometimes|required|string|max:200',
            'task_description' => 'nullable|string',
            'priority' => 'sometimes|required|in:low,medium,high,critical',
            'estimated_hours' => 'nullable|integer|min:0|max:100',
            'frequency' => 'sometimes|required|in:daily,weekly,monthly,yearly,as_needed',
            'is_core_responsibility' => 'boolean',
        ];
    }
}
