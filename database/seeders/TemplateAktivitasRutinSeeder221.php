<?php

namespace Database\Seeders;

use App\Models\ActivityTemplate221;
use App\Models\EmissionFactor;
use App\Models\User;
use Illuminate\Database\Seeder;

class TemplateAktivitasRutinSeeder221 extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'pengguna@carbontrace.id')->first() ?? User::first();
        if (!$user) {
            return;
        }

        $motor = EmissionFactor::where('name', 'like', '%Motor%')->first();
        $listrik = EmissionFactor::where('name', 'like', '%Listrik%')->first();
        $ac = EmissionFactor::where('name', 'like', '%AC%')->first();

        $templates = [
            [
                'user_id' => $user->id,
                'emission_factor_id' => $motor?->id ?? 1,
                'name' => 'Motor ke Kampus PP',
                'amount' => 15.0000,
                'estimated_emission' => 1.8000,
            ],
            [
                'user_id' => $user->id,
                'emission_factor_id' => $ac?->id ?? $motor?->id ?? 1,
                'name' => 'AC Kamar Semalaman',
                'amount' => 8.0000,
                'estimated_emission' => 2.6000,
            ],
            [
                'user_id' => $user->id,
                'emission_factor_id' => $listrik?->id ?? 2,
                'name' => 'Listrik Harian Kos',
                'amount' => 5.0000,
                'estimated_emission' => 1.9750,
            ],
        ];

        foreach ($templates as $t) {
            ActivityTemplate221::firstOrCreate(
                ['user_id' => $t['user_id'], 'name' => $t['name']],
                $t
            );
        }
    }
}
