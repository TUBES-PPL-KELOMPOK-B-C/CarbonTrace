<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\EmissionFactor;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MasterDataEmisiStubSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Roles
        $roleUser = Role::firstOrCreate(['name' => 'user']);
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);

        // 2. Buat Pengguna Uji (User & Admin)
        $user = User::firstOrCreate(
            ['email' => 'pengguna@carbontrace.id'],
            [
                'name' => 'Hadi Dwi Pranoto',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole($roleUser);

        // Pengaturan rumah tangga (household_size = 2 untuk pembagian listrik)
        UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            ['household_size' => 2]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@carbontrace.id'],
            [
                'name' => 'Administrator CarbonTrace',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($roleAdmin);

        // 3. Kategori Emisi
        $catTransport = Category::firstOrCreate(['code' => 'transportasi'], [
            'name' => 'Transportasi',
            'icon' => 'truck',
            'is_active' => true,
        ]);

        $catListrik = Category::firstOrCreate(['code' => 'listrik'], [
            'name' => 'Listrik & Energi',
            'icon' => 'bolt',
            'is_active' => true,
        ]);

        $catMakanan = Category::firstOrCreate(['code' => 'makanan'], [
            'name' => 'Makanan',
            'icon' => 'utensils',
            'is_active' => true,
        ]);

        $catSampah = Category::firstOrCreate(['code' => 'sampah'], [
            'name' => 'Sampah & Limbah',
            'icon' => 'trash',
            'is_active' => true,
        ]);

        // 4. Faktor Emisi Utama
        $factors = [
            // Transportasi (unit: km)
            [
                'category_id' => $catTransport->id,
                'name' => 'Motor Bensin',
                'factor_value' => 0.1200,
                'unit' => 'km',
                'source_url' => 'https://kemenlh.go.id/faktor-emisi',
                'is_active' => true,
            ],
            [
                'category_id' => $catTransport->id,
                'name' => 'Mobil Bensin',
                'factor_value' => 0.2400,
                'unit' => 'km',
                'source_url' => 'https://kemenlh.go.id/faktor-emisi',
                'is_active' => true,
            ],
            [
                'category_id' => $catTransport->id,
                'name' => 'Bus Umum',
                'factor_value' => 0.0800,
                'unit' => 'km',
                'source_url' => 'https://kemenlh.go.id/faktor-emisi',
                'is_active' => true,
            ],
            // Listrik (unit: kWh)
            [
                'category_id' => $catListrik->id,
                'name' => 'Listrik Jaringan PLN (Jawa-Bali)',
                'factor_value' => 0.7900,
                'unit' => 'kWh',
                'source_url' => 'https://esdm.go.id/faktor-emisi-pln',
                'is_active' => true,
            ],
            [
                'category_id' => $catListrik->id,
                'name' => 'Pendingin Ruangan (AC 1 PK)',
                'factor_value' => 0.6500,
                'unit' => 'jam',
                'source_url' => 'https://esdm.go.id',
                'is_active' => true,
            ],
            // Makanan (unit: porsi atau kg)
            [
                'category_id' => $catMakanan->id,
                'name' => 'Daging Sapi',
                'factor_value' => 2.5000,
                'unit' => 'porsi',
                'source_url' => 'https://ourworldindata.org/carbon-footprint-food',
                'is_active' => true,
            ],
            [
                'category_id' => $catMakanan->id,
                'name' => 'Daging Ayam',
                'factor_value' => 0.8500,
                'unit' => 'porsi',
                'source_url' => 'https://ourworldindata.org/carbon-footprint-food',
                'is_active' => true,
            ],
            [
                'category_id' => $catMakanan->id,
                'name' => 'Nasi & Menu Nabati',
                'factor_value' => 0.3000,
                'unit' => 'porsi',
                'source_url' => 'https://ourworldindata.org/carbon-footprint-food',
                'is_active' => true,
            ],
            // Sampah (unit: kg)
            [
                'category_id' => $catSampah->id,
                'name' => 'Sampah Organik Rumah Tangga',
                'factor_value' => 0.4500,
                'unit' => 'kg',
                'source_url' => 'https://kemenlh.go.id/sampah',
                'is_active' => true,
            ],
            // Faktor Emisi Tidak Aktif (Arsip untuk uji validasi)
            [
                'category_id' => $catTransport->id,
                'name' => 'Kendaraan Tua (Diarsipkan)',
                'factor_value' => 0.9900,
                'unit' => 'km',
                'source_url' => 'https://kemenlh.go.id',
                'is_active' => false,
            ],
        ];

        foreach ($factors as $f) {
            EmissionFactor::firstOrCreate(
                ['category_id' => $f['category_id'], 'name' => $f['name']],
                $f
            );
        }
    }
}
