<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Data dummy PBI-09 Manajemen Acuan Konversi Satuan (modul Renaya-154), dibuat di branch
// Solving_Project_teams supaya halaman /admin/conversion-units tidak kosong saat demo.
// Faktor emisi dicari lewat namanya dari MasterDataEmisiStubSeeder; baris yang faktornya
// belum ada dilewati. Aman dijalankan berulang (updateOrInsert per nama).
class AcuanKonversiSatuanSeeder154 extends Seeder
{
    private const PORSI = [
        // [nama porsi, nama faktor emisi, gram per porsi]
        ['1 potong rendang', 'Daging Sapi', 80],
        ['1 porsi sate sapi (10 tusuk)', 'Daging Sapi', 150],
        ['1 potong ayam goreng', 'Daging Ayam', 100],
        ['1 porsi soto ayam', 'Daging Ayam', 60],
        ['1 piring nasi putih', 'Nasi & Menu Nabati', 150],
        ['1 porsi gado-gado', 'Nasi & Menu Nabati', 250],
    ];

    private const PERANGKAT = [
        // [nama perangkat, nama faktor emisi, daya dalam watt]
        ['Lampu LED', 'Listrik Jaringan PLN (Jawa-Bali)', 10],
        ['Kipas angin', 'Listrik Jaringan PLN (Jawa-Bali)', 45],
        ['Televisi LED 32 inci', 'Listrik Jaringan PLN (Jawa-Bali)', 60],
        ['Kulkas satu pintu', 'Listrik Jaringan PLN (Jawa-Bali)', 90],
        ['Rice cooker (memasak)', 'Listrik Jaringan PLN (Jawa-Bali)', 350],
        ['Setrika', 'Listrik Jaringan PLN (Jawa-Bali)', 300],
        ['Laptop', 'Listrik Jaringan PLN (Jawa-Bali)', 65],
    ];

    public function run(): void
    {
        $faktor = DB::table('emission_factors')->pluck('id', 'name');

        $this->isi('portion_units', self::PORSI, 'gram_per_portion', $faktor);
        $this->isi('appliances', self::PERANGKAT, 'wattage', $faktor);
    }

    private function isi(string $tabel, array $baris, string $kolomAngka, $faktor): void
    {
        if (! Schema::hasTable($tabel)) {
            return;
        }

        foreach ($baris as [$nama, $namaFaktor, $angka]) {
            if (! isset($faktor[$namaFaktor])) {
                continue;
            }

            DB::table($tabel)->updateOrInsert(
                ['name' => $nama],
                ['emission_factor_id' => $faktor[$namaFaktor], $kolomAngka => $angka, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }
}
