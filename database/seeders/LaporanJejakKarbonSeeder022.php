<?php

namespace Database\Seeders;

use App\Models\SavedReport022;
use App\Models\User;
use App\Services\RingkasanTarget022;
use Illuminate\Database\Seeder;

// PBI-12: satu laporan 30 hari terakhir untuk akun uji Modul 2.
class LaporanJejakKarbonSeeder022 extends Seeder
{
    public function run(): void
    {
        $pengguna = User::where('email', 'pengguna@carbontrace.id')->first();

        if (! $pengguna) {
            return;
        }

        $hariIni = RingkasanTarget022::hariIni();

        SavedReport022::firstOrCreate(
            ['user_id' => $pengguna->id, 'title' => 'Laporan 30 hari terakhir'],
            ['period_start' => $hariIni->copy()->subDays(29)->toDateString(), 'period_end' => $hariIni->toDateString(), 'category_scope' => ''],
        );
    }
}
