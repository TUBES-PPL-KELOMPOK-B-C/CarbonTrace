<?php

namespace Database\Seeders;

use App\Models\Reminder022;
use App\Models\User;
use Illuminate\Database\Seeder;

// PBI-11: dua pengingat aktif dan satu nonaktif untuk akun uji Modul 2.
class PengingatPencatatanHarianSeeder022 extends Seeder
{
    public function run(): void
    {
        $pengguna = User::where('email', 'pengguna@carbontrace.id')->first();

        if (! $pengguna) {
            return;
        }

        foreach ([['07:30', true], ['20:00', true], ['12:15', false]] as [$jam, $aktif]) {
            Reminder022::firstOrCreate(
                ['user_id' => $pengguna->id, 'remind_time' => $jam.':00'],
                ['is_active' => $aktif],
            );
        }
    }
}
