<?php

namespace Database\Seeders;

use App\Models\Activitie221;
use App\Models\EmissionFactor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PencatatanAktivitasEmisiHarianSeeder221 extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'pengguna@carbontrace.id')->first() ?? User::first();
        if (!$user) {
            return;
        }

        $motor = EmissionFactor::where('name', 'like', '%Motor%')->first();
        $listrik = EmissionFactor::where('name', 'like', '%Listrik%')->first();
        $ayam = EmissionFactor::where('name', 'like', '%Ayam%')->first();

        $today = Carbon::today();

        $data = [
            [
                'user_id' => $user->id,
                'emission_factor_id' => $motor?->id ?? 1,
                'amount' => 15.0000,
                'emission' => 1.8000,
                'activity_date' => $today->toDateString(),
                'note' => 'Perjalanan ke kampus PP',
            ],
            [
                'user_id' => $user->id,
                'emission_factor_id' => $ayam?->id ?? 2,
                'amount' => 2.0000,
                'emission' => 1.7000,
                'activity_date' => $today->toDateString(),
                'note' => 'Makan siang dan malam',
            ],
            [
                'user_id' => $user->id,
                'emission_factor_id' => $listrik?->id ?? 3,
                'amount' => 6.0000,
                'emission' => 2.3700,
                'activity_date' => $today->copy()->subDay()->toDateString(),
                'note' => 'Pemakaian listrik kemarin',
            ],
            [
                'user_id' => $user->id,
                'emission_factor_id' => $motor?->id ?? 1,
                'amount' => 12.5000,
                'emission' => 1.5000,
                'activity_date' => $today->copy()->subDays(2)->toDateString(),
                'note' => 'Beli buku dan makan malam',
            ],
        ];

        foreach ($data as $d) {
            Activitie221::create($d);
        }
    }
}
