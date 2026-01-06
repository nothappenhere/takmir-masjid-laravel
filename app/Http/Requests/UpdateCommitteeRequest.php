<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommitteeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $committeeId = $this->route('committee');

        return [
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|max:200|unique:committees,email,' . $committeeId,
            'phone_number' => 'sometimes|nullable|string|max:20|unique:committees,phone_number,' . $committeeId,
            'gender' => 'sometimes|required|in:male,female',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'join_date' => 'nullable|date|after_or_equal:birth_date',
            'active_status' => 'sometimes|required|in:active,inactive,resigned',
            'position_id' => 'nullable|exists:positions,id',
            'user_id' => 'nullable|exists:users,id',
            'photo_path' => 'nullable|string|max:500',
        ];
    }
}
