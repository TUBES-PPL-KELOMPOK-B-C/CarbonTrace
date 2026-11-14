<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimpanPapanPeringkatKomunitas314 extends FormRequest
{
    /**
     * Menentukan apakah pengguna berwenang membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return auth()->check() && (bool) auth()->user()->is_active;
    }

    /**
     * Aturan validasi untuk filter dan pagination papan peringkat.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'periode' => ['nullable', 'string', 'in:minggu,bulan,semua'],
            'urut' => ['nullable', 'string', 'in:poin,streak,penurunan'],
            'halaman' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia sesuai spesifikasi.
     */
    public function messages(): array
    {
        return [
            'periode.in' => 'Parameter periode hanya menerima: minggu, bulan, semua.',
            'urut.in' => 'Parameter urutan hanya menerima: poin, streak, penurunan.',
            'halaman.integer' => 'Nomor halaman harus berupa bilangan bulat.',
            'halaman.min' => 'Nomor halaman bilangan bulat positif.',
        ];
    }
}
