<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $positionId = $this->route('position');

        return [
            'name' => 'sometimes|required|string|max:100|unique:positions,name,' . $positionId,
            'slug' => 'nullable|string|max:120|unique:positions,slug,' . $positionId,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:positions,id',
            'order' => 'nullable|integer|min:0',
            'status' => 'sometimes|required|in:active,inactive',
            'level' => 'sometimes|required|in:leadership,division_head,staff,volunteer',
        ];
    }
}
