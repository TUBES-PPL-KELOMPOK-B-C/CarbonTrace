<?php

namespace App\Http\Requests;

use App\Models\User314;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanManajemenProfilPengguna314 extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User314::class)->ignore($this->user()->id),
            ],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'campus' => ['nullable', 'string', 'max:120'],
            'study_program' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'email.required' => 'Surel wajib diisi.',
            'email.email' => 'Format surel tidak valid.',
            'email.unique' => 'Surel ini sudah dipakai akun lain.',
            'photo.image' => 'Berkas harus berupa gambar.',
            'photo.mimes' => 'Format foto harus berupa JPG atau PNG.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'campus.max' => 'Asal kampus maksimal 120 karakter.',
            'study_program.max' => 'Program studi maksimal 120 karakter.',
        ];
    }
}
