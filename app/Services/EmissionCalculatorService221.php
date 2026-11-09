<?php

namespace App\Services;

use App\Models\EmissionFactor;
use App\Models\User;
use App\Models\UserSetting;
use InvalidArgumentException;

class EmissionCalculatorService221
{
    /**
     * Hitung emisi berdasarkan faktor emisi, jumlah, dan pengguna.
     * Logika:
     * - emisi = amount x factor_value
     * - Untuk kategori listrik, hasilnya dibagi household_size dari user_settings (PBI-02)
     * - Hasil dibulatkan ke 4 angka desimal untuk penyimpanan, dan 2 desimal untuk pratinjau
     *
     * @param int|EmissionFactor $factor
     * @param float $amount
     * @param int|User|null $user
     * @return array
     */
    public function calculate($factor, float $amount, $user = null): array
    {
        if (is_numeric($factor)) {
            $factor = EmissionFactor::with('category')->findOrFail($factor);
        }

        if (!$factor->is_active) {
            throw new InvalidArgumentException('Faktor emisi ini sudah diarsipkan, pilih yang lain.');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('Jumlah harus lebih besar dari nol.');
        }

        if ($amount > 100000) {
            throw new InvalidArgumentException('Jumlah maksimal adalah 100.000.');
        }

        $factorValue = (float) $factor->factor_value;
        $totalEmission = $amount * $factorValue;

        // Cek kategori listrik (dibagi household_size)
        $isListrik = false;
        $categoryName = strtolower($factor->category->name ?? '');
        $categoryCode = strtolower($factor->category->code ?? '');
        if (str_contains($categoryName, 'listrik') || str_contains($categoryCode, 'listrik') || str_contains(strtolower($factor->unit), 'kwh')) {
            $isListrik = true;
            $householdSize = 1;

            $userId = $user instanceof User ? $user->id : $user;
            if ($userId) {
                $setting = UserSetting::where('user_id', $userId)->first();
                if ($setting && $setting->household_size > 0) {
                    $householdSize = $setting->household_size;
                }
            }

            if ($householdSize > 1) {
                $totalEmission = $totalEmission / $householdSize;
            }
        }

        $emissionExact = round($totalEmission, 4);
        $emissionDisplay = round($totalEmission, 2);

        // Format angka Indonesia (koma untuk desimal)
        $jumlahFormatted = str_replace('.', ',', (string) round($amount, 2));
        $nilaiFaktorFormatted = str_replace('.', ',', (string) $factorValue);

        $rincian = "{$jumlahFormatted} {$factor->unit} x {$nilaiFaktorFormatted} kg CO2e/{$factor->unit}";
        if ($isListrik && isset($householdSize) && $householdSize > 1) {
            $rincian .= " : {$householdSize} orang";
        }

        return [
            'emisi' => $emissionDisplay,
            'emisi_persis' => $emissionExact,
            'satuan' => 'kg CO2e',
            'rincian' => $rincian,
            'faktor' => [
                'nama' => $factor->name,
                'nilai' => $factorValue,
                'sumber' => $factor->source_url ?? 'Katalog Emisi Nasional / IPCC',
            ],
        ];
    }
}
