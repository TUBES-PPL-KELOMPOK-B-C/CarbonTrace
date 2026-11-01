<?php

namespace App\Http\Requests;

use App\Http\Controllers\KalkulatorPublik111;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class HitungKalkulator111 extends FormRequest
{
    private ?object $faktor = null;

    public function authorize(): bool
    {
        return true;
    }

    // Orang Indonesia menulis 1,5 bukan 1.5.
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('amount'))) {
            $this->merge(['amount' => str_replace(',', '.', trim($this->input('amount')))]);
        }
    }

    public function rules(): array
    {
        return [
            'emission_factor_id' => ['required', 'integer', function (string $atribut, mixed $nilai, Closure $gagal) {
                if (! $this->faktor()) {
                    $gagal('Jenis aktivitas ini tidak tersedia. Pilih yang lain.');
                }
            }],
            'amount' => ['required', 'numeric', 'gt:0', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'emission_factor_id.required' => 'Pilih kategori dan jenis aktivitasnya dulu.',
            'emission_factor_id.integer' => 'Pilih kategori dan jenis aktivitasnya dulu.',
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.numeric' => 'Jumlah harus berupa angka.',
            'amount.gt' => 'Jumlah harus lebih besar dari 0.',
            'amount.max' => 'Jumlah maksimal 100.000.',
        ];
    }

    // Faktor emisi aktif yang dipilih, atau null bila tidak ada.
    public function faktor(): ?object
    {
        return $this->faktor ??= KalkulatorPublik111::faktorAktif()
            ->firstWhere('id', (int) $this->input('emission_factor_id'));
    }
}
