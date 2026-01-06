<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:positions,name',
            'slug' => 'nullable|string|max:120|unique:positions,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:positions,id',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'level' => 'required|in:leadership,division_head,staff,volunteer',
        ];
    }
}
