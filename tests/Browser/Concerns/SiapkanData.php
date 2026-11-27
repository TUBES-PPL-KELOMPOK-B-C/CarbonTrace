<?php

namespace Tests\Browser\Concerns;

use App\Models\QuizQuestion221;
use App\Models\User314;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

// Data bantu untuk tes browser. Semua dibuat lewat database tes (database/dusk.sqlite), yang
// dikosongkan ulang oleh DatabaseMigrations di setiap tes.
trait SiapkanData
{
    protected const SANDI = 'Rahasia123!';

    // Urutan sama dengan seeder: administrator = id 1 (PastikanAdministrator314 juga memeriksa role_id 1).
    protected function siapkanPeran(): void
    {
        Role::findOrCreate('administrator', 'web');
        Role::findOrCreate('pengguna', 'web');
    }

    // Pengguna biasa. Kuis awal ditandai sudah dilewati, supaya masuk lewat formulir langsung ke dashboard.
    protected function buatPengguna(array $atribut = [], bool $lewatiKuis = true): User314
    {
        $this->siapkanPeran();
        $pengguna = User314::factory()->create($atribut + [
            'password' => self::SANDI,
            'role_id' => Role::findByName('pengguna', 'web')->id,
            'is_active' => true,
        ]);
        $pengguna->assignRole('pengguna');

        if ($lewatiKuis) {
            DB::table('users')->where('id', $pengguna->id)->update(['quiz_skipped_at' => now()]);
        }

        return $pengguna->fresh();
    }

    protected function buatAdmin(array $atribut = []): User314
    {
        $this->siapkanPeran();
        $admin = User314::factory()->create($atribut + [
            'name' => 'Administrator Uji',
            'password' => self::SANDI,
            'role_id' => Role::findByName('administrator', 'web')->id,
            'is_active' => true,
        ]);
        $admin->assignRole('administrator');

        return $admin;
    }

    /** @return array{listrik: int, motor: int, kategoriListrik: int} id faktor emisi */
    protected function siapkanMasterEmisi(): array
    {
        $listrik = DB::table('categories')->insertGetId(['name' => 'Listrik', 'code' => 'listrik', 'icon' => 'zap', 'is_active' => true]);
        $transportasi = DB::table('categories')->insertGetId(['name' => 'Transportasi', 'code' => 'transportasi', 'icon' => 'truck', 'is_active' => true]);

        return [
            'kategoriListrik' => $listrik,
            'listrik' => DB::table('emission_factors')->insertGetId(['category_id' => $listrik, 'name' => 'Listrik PLN Uji', 'factor_value' => 0.79, 'unit' => 'kWh', 'is_active' => true]),
            'motor' => DB::table('emission_factors')->insertGetId(['category_id' => $transportasi, 'name' => 'Motor Bensin Uji', 'factor_value' => 0.1, 'unit' => 'km', 'is_active' => true]),
        ];
    }

    protected function catatAktivitas(User314 $pemilik, int $faktor, float $emisi, string $tanggal): void
    {
        DB::table('activities')->insert([
            'user_id' => $pemilik->id, 'emission_factor_id' => $faktor, 'amount' => 1, 'emission' => $emisi,
            'activity_date' => $tanggal, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    // Dua soal aktif: jawaban terberat bernilai 3 x 2 + 4 x 1,5 = 12 kg CO2e per hari.
    protected function siapkanSoalKuis(): void
    {
        QuizQuestion221::create(['question' => 'Bagaimana kamu bepergian sehari-hari?', 'emission_weight' => 2, 'is_active' => true,
            'options' => [['label' => 'Jalan kaki', 'weight' => 0], ['label' => 'Sepeda motor', 'weight' => 3]]]);
        QuizQuestion221::create(['question' => 'Berapa jam AC menyala setiap hari?', 'emission_weight' => 1.5, 'is_active' => true,
            'options' => [['label' => 'Tidak memakai AC', 'weight' => 0], ['label' => 'Lebih dari 8 jam', 'weight' => 4]]]);
    }
}
