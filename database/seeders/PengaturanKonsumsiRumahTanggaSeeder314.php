<?php

namespace Database\Seeders;

use App\Models\User314;
use App\Models\UserSetting314;
use Illuminate\Database\Seeder;

class PengaturanKonsumsiRumahTanggaSeeder314 extends Seeder
{
    public function run(): void
    {
        $user = User314::where('email', 'pengguna@carbontrace.id')->first();
        if ($user) {
            UserSetting314::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'household_size' => 4,
                    'power_watt' => 1300,
                    'vehicle_ownership' => 'motor',
                ]
            );
        }

        $admin = User314::where('email', 'admin@carbontrace.id')->first();
        if ($admin) {
            UserSetting314::firstOrCreate(
                ['user_id' => $admin->id],
                [
                    'household_size' => 3,
                    'power_watt' => 2200,
                    'vehicle_ownership' => 'keduanya',
                ]
            );
        }
    }
}
