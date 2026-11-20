<?php

namespace Database\Seeders;

use App\Models\User314;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PapanPeringkatKomunitasSeeder314 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role default ada
        $rolePengguna = DB::table('roles')->where('name', 'pengguna')->first();
        $roleId = $rolePengguna ? $rolePengguna->id : 1;

        // Daftar pengguna dummy untuk papan peringkat
        $usersData = [
            [
                'name' => 'Rani Paramitha',
                'email' => 'rani.paramitha@carbontrace.id',
                'points' => 2450,
                'streak_count' => 24,
                'mingguan_poin' => 650,
                'bulanan_poin' => 1800,
                'progress' => 22.5,
            ],
            [
                'name' => 'Dimas Arya Pratama',
                'email' => 'dimas.arya@carbontrace.id',
                'points' => 2180,
                'streak_count' => 19,
                'mingguan_poin' => 450,
                'bulanan_poin' => 1650,
                'progress' => 18.0,
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@carbontrace.id',
                'points' => 1920,
                'streak_count' => 15,
                'mingguan_poin' => 700, // Siti memimpin mingguan
                'bulanan_poin' => 1400,
                'progress' => 15.2,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@carbontrace.id',
                'points' => 1450,
                'streak_count' => 11,
                'mingguan_poin' => 250,
                'bulanan_poin' => 950,
                'progress' => 10.5,
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@carbontrace.id',
                'points' => 1100,
                'streak_count' => 8,
                'mingguan_poin' => 150,
                'bulanan_poin' => 750,
                'progress' => 8.0,
            ],
            [
                'name' => 'Nadia Putri',
                'email' => 'nadia.putri@carbontrace.id',
                'points' => 820,
                'streak_count' => 5,
                'mingguan_poin' => 100,
                'bulanan_poin' => 500,
                'progress' => 5.4,
            ],
            [
                'name' => 'Pengguna Baru Nol Poin',
                'email' => 'nol.poin@carbontrace.id',
                'points' => 0,
                'streak_count' => 0,
                'mingguan_poin' => 0,
                'bulanan_poin' => 0,
                'progress' => 0.0,
            ],
        ];

        foreach ($usersData as $data) {
            $user = User314::firstOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $roleId,
                    'name' => $data['name'],
                    'password' => Hash::make('Password123!'),
                    'campus' => 'Telkom University',
                    'study_program' => 'S1 Rekayasa Perangkat Lunak',
                    'points' => $data['points'],
                    'streak_count' => $data['streak_count'],
                    'is_active' => true,
                    'created_at' => now()->subMonths(2),
                    'updated_at' => now(),
                ]
            );

            // Buat entri point_logs jika tabel tersedia
            if (Schema::hasTable('point_logs')) {
                // Log mingguan (dibuat 2 hari lalu)
                if ($data['mingguan_poin'] > 0) {
                    DB::table('point_logs')->insert([
                        'user_id' => $user->id,
                        'points' => $data['mingguan_poin'],
                        'reason' => 'Pencatatan emisi transportasi mingguan',
                        'created_at' => now()->subDays(2),
                    ]);
                }

                // Log bulanan (dibuat 15 hari lalu)
                $selisihBulan = $data['bulanan_poin'] - $data['mingguan_poin'];
                if ($selisihBulan > 0) {
                    DB::table('point_logs')->insert([
                        'user_id' => $user->id,
                        'points' => $selisihBulan,
                        'reason' => 'Pencatatan emisi listrik bulanan',
                        'created_at' => now()->subDays(15),
                    ]);
                }

                // Log lampau sepanjang waktu (dibuat 45 hari lalu)
                $selisihTotal = $data['points'] - $data['bulanan_poin'];
                if ($selisihTotal > 0) {
                    DB::table('point_logs')->insert([
                        'user_id' => $user->id,
                        'points' => $selisihTotal,
                        'reason' => 'Penyelesaian tantangan gaya hidup hijau awal',
                        'created_at' => now()->subDays(45),
                    ]);
                }
            }

            // Buat entri challenge_user untuk progres penurunan jika tabel tersedia
            if (Schema::hasTable('challenge_user') && Schema::hasTable('challenges')) {
                $challenge = DB::table('challenges')->first();
                if (!$challenge) {
                    $challengeId = DB::table('challenges')->insertGetId([
                        'title' => 'Tantangan Kurangi Emisi Listrik 20%',
                        'description' => 'Matikan perangkat listrik tidak terpakai selama 30 hari berturut-turut.',
                        'points' => 500,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $challengeId = $challenge->id;
                }

                DB::table('challenge_user')->updateOrInsert(
                    ['user_id' => $user->id, 'challenge_id' => $challengeId],
                    [
                        'status' => 'aktif',
                        'progress' => $data['progress'],
                        'joined_at' => now()->subDays(20),
                        'created_at' => now()->subDays(20),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
