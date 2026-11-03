<?php

namespace App\Http\Requests;

use App\Models\EmissionFactor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanTemplateAktivitasRutin221 extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $templateId = $this->route('template')?->id ?? $this->route('template');

        return [
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('activity_templates', 'name')
                    ->where(fn ($query) => $query->where('user_id', auth()->id()))
                    ->ignore($templateId),
            ],
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
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama template wajib diisi.',
            'name.max' => 'Nama template maksimal 80 karakter.',
            'name.unique' => 'Kamu sudah punya template dengan nama ini.',
            'emission_factor_id.required' => 'Faktor emisi wajib dipilih.',
            'emission_factor_id.exists' => 'Faktor emisi yang dipilih tidak valid.',
            'amount.required' => 'Jumlah aktivitas wajib diisi.',
            'amount.numeric' => 'Jumlah harus berupa angka.',
            'amount.gt' => 'Jumlah harus lebih besar dari nol.',
            'amount.max' => 'Jumlah maksimal adalah 100.000.',
        ];
    }
}
