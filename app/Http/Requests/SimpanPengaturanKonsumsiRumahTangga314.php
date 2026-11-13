<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanPengaturanKonsumsiRumahTangga314 extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'household_size' => ['required', 'integer', 'min:1', 'max:20'],
            'power_watt' => ['required', 'integer', 'min:450', 'max:50000'],
            'vehicle_ownership' => [
                'required',
                'string',
                Rule::in(['tidak ada', 'motor', 'mobil', 'keduanya']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'household_size.required' => 'Jumlah penghuni wajib diisi.',
            'household_size.integer' => 'Jumlah penghuni harus berupa angka.',
            'household_size.min' => 'Jumlah penghuni antara 1 sampai 20 orang.',
            'household_size.max' => 'Jumlah penghuni antara 1 sampai 20 orang.',
            'power_watt.required' => 'Daya listrik wajib diisi.',
            'power_watt.integer' => 'Daya listrik harus berupa angka.',
            'power_watt.min' => 'Daya listrik antara 450 sampai 50000 watt.',
            'power_watt.max' => 'Daya listrik antara 450 sampai 50000 watt.',
            'vehicle_ownership.required' => 'Kepemilikan kendaraan wajib dipilih.',
            'vehicle_ownership.in' => 'Pilihan kepemilikan kendaraan harus salah satu dari: tidak ada, motor, mobil, atau keduanya.',
        ];
    }
}
