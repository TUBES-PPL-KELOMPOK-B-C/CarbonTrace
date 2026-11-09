<?php

namespace App\Http\Requests;

use App\Models\EmissionFactor;
use Illuminate\Foundation\Http\FormRequest;

class SimpanPencatatanAktivitasEmisiHarian221 extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'emission_factor_id' => [
                'required',
                'exists:emission_factors,id',
                function ($attribute, $value, $fail) {
                    $factor = EmissionFactor::find($value);
                    if ($factor && !$factor->is_active) {
                        $fail('Faktor emisi ini sudah diarsipkan, pilih yang lain.');
                    }
                },
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'emission_factor_id.required' => 'Faktor emisi wajib dipilih.',
            'emission_factor_id.exists' => 'Faktor emisi yang dipilih tidak valid.',
            'amount.required' => 'Jumlah aktivitas wajib diisi.',
            'amount.numeric' => 'Jumlah harus berupa angka.',
            'amount.gt' => 'Jumlah harus lebih besar dari nol.',
            'amount.max' => 'Jumlah maksimal adalah 100.000.',
            'activity_date.required' => 'Tanggal aktivitas wajib diisi.',
            'activity_date.date' => 'Format tanggal tidak sah.',
            'activity_date.before_or_equal' => 'Tanggal aktivitas tidak boleh melewati hari ini.',
            'note.max' => 'Catatan maksimal 255 karakter.',
        ];
    }
}
