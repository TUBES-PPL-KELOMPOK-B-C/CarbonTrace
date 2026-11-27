<?php

namespace App\Services;

use App\Models\Appliance;
use App\Models\EmissionFactor;
use App\Models\PortionUnit;

class EmissionCalculator
{
    /**
     * Menghitung total emisi (kg CO2e) dari input aktivitas pengguna.
     *
     * @param  int         $emissionFactorId
     * @param  float       $quantity         Nilai input (jumlah satuan baku, jumlah porsi, atau durasi jam)
     * @param  int|null    $portionUnitId    Opsional: ID acuan konversi porsi makanan
     * @param  int|null    $applianceId      Opsional: ID perangkat listrik (Watt)
     * @return float                         Total emisi dalam kg CO2e (4 desimal)
     */
    public static function calculate(
        int $emissionFactorId,
        float $quantity,
        ?int $portionUnitId = null,
        ?int $applianceId = null
    ): float {
        $factor = EmissionFactor::findOrFail($emissionFactorId);

        if ($quantity <= 0) {
            return 0.0;
        }

        // 1. Konversi satuan porsi (gram -> kg) jika faktor emisi memakai satuan kg/gram
        if ($portionUnitId !== null) {
            $portion = PortionUnit::findOrFail($portionUnitId);
            $totalGrams = $quantity * (float) $portion->gram_per_portion;

            $standardizedQty = strtolower($factor->unit) === 'gram' || strtolower($factor->unit) === 'g'
                ? $totalGrams
                : $totalGrams / 1000;

            return round($standardizedQty * (float) $factor->factor_value, 4);
        }

        // 2. Konversi daya perangkat listrik: (Watt * Jam) / 1000 = kWh
        if ($applianceId !== null) {
            $appliance = Appliance::findOrFail($applianceId);
            $kWh = ((float) $appliance->wattage * $quantity) / 1000;

            return round($kWh * (float) $factor->factor_value, 4);
        }

        // 3. Perhitungan langsung menggunakan satuan baku faktor emisi
        return round($quantity * (float) $factor->factor_value, 4);
    }
}