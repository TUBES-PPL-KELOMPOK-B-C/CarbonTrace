<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimpanManajemenBankPertanyaanKuis221 extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->is_admin ?? false);
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:10', 'max:500'],
            'options' => [
                'required',
                'array',
                'min:2',
                'max:6',
                function ($attribute, $value, $fail) {
                    if (!is_array($value) || count($value) < 2) {
                        return;
                    }
                    $totalWeight = 0;
                    foreach ($value as $opt) {
                        $totalWeight += (float) ($opt['weight'] ?? 0);
                    }
                    if ($totalWeight <= 0) {
                        $fail('Semua bobot pilihan tidak boleh bernilai nol.');
                    }
                },
            ],
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.weight' => ['required', 'numeric', 'min:0'],
            'emission_weight' => ['required', 'numeric', 'min:0', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Pertanyaan kuis wajib diisi.',
            'question.min' => 'Pertanyaan kuis minimal 10 karakter.',
            'question.max' => 'Pertanyaan kuis maksimal 500 karakter.',
            'options.required' => 'Pilihan jawaban wajib diisi.',
            'options.min' => 'Pertanyaan butuh minimal dua pilihan jawaban.',
            'options.max' => 'Pertanyaan maksimal memiliki enam pilihan jawaban.',
            'options.*.label.required' => 'Setiap pilihan wajib memiliki label teks.',
            'options.*.weight.required' => 'Setiap pilihan wajib memiliki nilai bobot angka.',
            'options.*.weight.numeric' => 'Bobot harus berupa angka.',
            'emission_weight.required' => 'Bobot emisi pertanyaan wajib diisi.',
            'emission_weight.min' => 'Bobot emisi tidak boleh negatif.',
            'emission_weight.max' => 'Bobot emisi maksimal 1.000.',
        ];
    }
}
