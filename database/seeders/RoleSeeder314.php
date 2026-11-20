<?php

namespace Database\Seeders;

use App\Models\User314;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder314 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat peran 'administrator' dan 'pengguna'
        $adminRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);

        // 2. Buat akun administrator awal
        $admin = User314::firstOrCreate(
            ['email' => 'admin@carbontrace.id'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Administrator CarbonTrace',
                'password' => Hash::make('Admin123!'),
                'campus' => 'Telkom University',
                'study_program' => 'S1 Rekayasa Perangkat Lunak',
                'points' => 0,
                'streak_count' => 0,
                'is_active' => true,
            ]
        );

        if (method_exists($admin, 'assignRole') && !$admin->hasRole('administrator')) {
            $admin->assignRole('administrator');
        }

        // 3. Buat akun pengguna reguler awal
        $pengguna = User314::firstOrCreate(
            ['email' => 'pengguna@carbontrace.id'],
            [
                'role_id' => $userRole->id,
                'name' => 'Pengguna CarbonTrace',
                'password' => Hash::make('Password123!'),
                'campus' => 'Telkom University',
                'study_program' => 'S1 Rekayasa Perangkat Lunak',
                'points' => 50,
                'streak_count' => 3,
                'is_active' => true,
            ]
        );

        if (method_exists($pengguna, 'assignRole') && !$pengguna->hasRole('pengguna')) {
            $pengguna->assignRole('pengguna');
        }
    }
}
