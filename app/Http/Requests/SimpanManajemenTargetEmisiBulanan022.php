<?php

namespace App\Http\Requests;

use App\Rules\PeriodeTidakTumpangTindih022;
use App\Services\RingkasanTarget022;
use Illuminate\Foundation\Http\FormRequest;

class SimpanManajemenTargetEmisiBulanan022 extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Orang Indonesia menulis 150,5 bukan 150.5.
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('emission_limit'))) {
            $this->merge(['emission_limit' => str_replace(',', '.', trim($this->input('emission_limit')))]);
        }
    }

    public function rules(): array
    {
        $target = $this->route('target');

        return [
            'period_start' => [
                'bail', 'required', 'date_format:Y-m-d',
                new PeriodeTidakTumpangTindih022($this->user()->id, $target?->id),
            ],
            'period_end' => [
                'bail', 'required', 'date_format:Y-m-d', 'after:period_start',
                'after_or_equal:'.RingkasanTarget022::hariIni()->toDateString(),
            ],
            'emission_limit' => ['required', 'numeric', 'gt:0', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'period_start.required' => 'Tanggal mulai wajib diisi.',
            'period_start.date_format' => 'Tanggal mulai belum benar.',
            'period_end.required' => 'Tanggal selesai wajib diisi.',
            'period_end.date_format' => 'Tanggal selesai belum benar.',
            'period_end.after' => 'Tanggal selesai harus setelah tanggal mulai.',
            'period_end.after_or_equal' => 'Periode ini sudah berakhir. Pilih periode yang masih berjalan atau yang akan datang.',
            'emission_limit.required' => 'Batas emisi wajib diisi.',
            'emission_limit.numeric' => 'Batas emisi harus berupa angka.',
            'emission_limit.gt' => 'Batas emisi harus lebih besar dari 0.',
            'emission_limit.max' => 'Batas emisi maksimal 10.000 kg CO2e.',
        ];
    }
}
