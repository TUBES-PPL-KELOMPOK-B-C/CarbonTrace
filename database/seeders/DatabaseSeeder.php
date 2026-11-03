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
            MasterDataEmisiStubSeeder::class,
            PencatatanAktivitasEmisiHarianSeeder221::class,
            TemplateAktivitasRutinSeeder221::class,
            ManajemenBankPertanyaanKuisSeeder221::class,
        ]);
    }
}
