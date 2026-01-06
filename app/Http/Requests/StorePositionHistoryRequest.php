<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'committee_id' => 'required|exists:committees,id',
            'position_id' => 'required|exists:positions,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'appointment_type' => 'required|in:permanent,temporary,acting',
            'remarks' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'committee_id.exists' => 'Pengurus tidak ditemukan',
            'position_id.exists' => 'Jabatan tidak ditemukan',
            'end_date.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal mulai',
            'appointment_type.in' => 'Tipe penunjukan harus permanent, temporary, atau acting',
        ];
    }
}
