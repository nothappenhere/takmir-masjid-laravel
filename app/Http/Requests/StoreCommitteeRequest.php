<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommitteeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ganti dengan authorization logic jika ada
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:200|unique:committees,email',
            'phone_number' => 'nullable|string|max:20|unique:committees,phone_number',
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'join_date' => 'nullable|date|after_or_equal:birth_date',
            'active_status' => 'required|in:active,inactive,resigned',
            'position_id' => 'nullable|exists:positions,id',
            'user_id' => 'nullable|exists:users,id',
            'photo_path' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap wajib diisi',
            'email.unique' => 'Email sudah terdaftar',
            'phone_number.unique' => 'Nomor telepon sudah terdaftar',
            'gender.in' => 'Jenis kelamin harus male atau female',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh lebih dari hari ini',
            'join_date.after_or_equal' => 'Tanggal bergabung tidak boleh sebelum tanggal lahir',
            'position_id.exists' => 'Jabatan tidak ditemukan',
        ];
    }
}
