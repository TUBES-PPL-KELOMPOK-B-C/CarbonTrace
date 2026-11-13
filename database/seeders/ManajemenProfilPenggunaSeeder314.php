<?php

namespace Database\Seeders;

use App\Models\User314;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ManajemenProfilPenggunaSeeder314 extends Seeder
{
    public function run(): void
    {
        $roleAdmin = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $rolePengguna = Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);
        $roleUser = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $roleAdminLegacy = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Admin User
        $admin = User314::firstOrCreate(
            ['email' => 'admin@carbontrace.id'],
            [
                'name' => 'Administrator CarbonTrace',
                'password' => Hash::make('password'),
                'role_id' => $roleAdmin->id,
                'campus' => 'Telkom University',
                'study_program' => 'S1 Informatika',
                'points' => 100,
                'streak_count' => 10,
                'is_active' => true,
            ]
        );
        $admin->assignRole($roleAdmin);
        $admin->assignRole($roleAdminLegacy);

        // Active Regular User
        $user = User314::firstOrCreate(
            ['email' => 'pengguna@carbontrace.id'],
            [
                'name' => 'Rosi Rahmawati',
                'password' => Hash::make('password'),
                'role_id' => $rolePengguna->id,
                'campus' => 'Telkom University',
                'study_program' => 'S1 Informatika',
                'points' => 50,
                'streak_count' => 5,
                'is_active' => true,
            ]
        );
        $user->assignRole($rolePengguna);
        $user->assignRole($roleUser);

        // Inactive User (for soft-delete / inactive login testing)
        $inactive = User314::firstOrCreate(
            ['email' => 'nonaktif@carbontrace.id'],
            [
                'name' => 'Pengguna Nonaktif',
                'password' => Hash::make('password'),
                'role_id' => $rolePengguna->id,
                'campus' => 'Telkom University',
                'study_program' => 'S1 Sistem Informasi',
                'points' => 0,
                'streak_count' => 0,
                'is_active' => false,
            ]
        );
        $inactive->assignRole($rolePengguna);
    }
}
