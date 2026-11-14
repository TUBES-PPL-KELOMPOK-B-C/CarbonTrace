<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder314::class,
            ManajemenProfilPenggunaSeeder314::class,
            PengaturanKonsumsiRumahTanggaSeeder314::class,
            PapanPeringkatKomunitasSeeder314::class,
            MasterDataEmisiStubSeeder::class,
            PencatatanAktivitasEmisiHarianSeeder221::class,
            TemplateAktivitasRutinSeeder221::class,
            ManajemenBankPertanyaanKuisSeeder221::class,
            // Modul Target dan Pengingat (Alvin-022)
            ManajemenTargetEmisiBulananSeeder022::class,
            PengingatPencatatanHarianSeeder022::class,
        ]);
    }
}
