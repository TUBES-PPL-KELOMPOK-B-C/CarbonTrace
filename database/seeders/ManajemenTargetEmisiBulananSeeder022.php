<?php

namespace Database\Seeders;

use App\Models\Target022;
use App\Models\User;
use App\Services\RingkasanTarget022;
use Illuminate\Database\Seeder;

// PBI-10: target bulan ini dan dua bulan sebelumnya untuk akun uji Modul 2.
class ManajemenTargetEmisiBulananSeeder022 extends Seeder
{
    public function run(): void
    {
        $pengguna = User::where('email', 'pengguna@carbontrace.id')->first();

        if (! $pengguna) {
            return;
        }

        $hariIni = RingkasanTarget022::hariIni();

        foreach ([[0, 120], [1, 150], [2, 140]] as [$mundur, $batas]) {
            $bulan = $hariIni->copy()->subMonthsNoOverflow($mundur);

            Target022::firstOrCreate(
                ['user_id' => $pengguna->id, 'period_start' => $bulan->copy()->startOfMonth()->toDateString()],
                ['period_end' => $bulan->copy()->endOfMonth()->toDateString(), 'emission_limit' => $batas],
            );
        }
    }
}
