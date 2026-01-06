<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePositionHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'committee_id' => 'sometimes|required|exists:committees,id',
            'position_id' => 'sometimes|required|exists:positions,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'appointment_type' => 'sometimes|required|in:permanent,temporary,acting',
            'remarks' => 'nullable|string',
        ];
    }
}
