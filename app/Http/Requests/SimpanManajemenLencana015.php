<?php

namespace App\Http\Requests;

use App\Models\Badge015;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// PBI-13: aturan isian formulir lencana, dipakai saat menambah maupun mengubah.
class SimpanManajemenLencana015 extends FormRequest
{
    // Kewenangan admin sudah dijaga middleware pada berkas rute dan Policy pada controller.
    public function authorize(): bool
    {
        return true;
    }

    // Orang Indonesia menulis 150,5 bukan 150.5.
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('required_value'))) {
            $this->merge(['required_value' => str_replace(',', '.', trim($this->input('required_value')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:120',
                // Nama lencana dipakai pengguna untuk mengenalinya, jadi tidak boleh kembar.
                Rule::unique('badges', 'name')->ignore($this->route('lencana')?->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['required', 'string', 'max:40'],
            'type' => ['required', Rule::in(array_keys(Badge015::JENIS))],
            'required_value' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lencana wajib diisi.',
            'name.max' => 'Nama lencana maksimal 120 karakter.',
            'name.unique' => 'Sudah ada lencana dengan nama ini.',
            'description.max' => 'Keterangan maksimal 500 karakter.',
            'icon.required' => 'Ikon lencana wajib dipilih.',
            'icon.max' => 'Ikon lencana maksimal 40 karakter.',
            'type.required' => 'Jenis syarat wajib dipilih.',
            'type.in' => 'Jenis syarat belum benar. Pilih poin, runtutan, aktivitas, atau emisi.',
            'required_value.required' => 'Angka syarat wajib diisi.',
            'required_value.numeric' => 'Angka syarat harus berupa angka.',
            'required_value.gt' => 'Angka syarat harus lebih besar dari 0.',
            'required_value.max' => 'Angka syarat maksimal 1.000.000.',
        ];
    }
}
