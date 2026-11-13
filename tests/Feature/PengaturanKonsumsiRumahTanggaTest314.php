<?php

namespace Tests\Feature;

use App\Models\User314;
use App\Models\UserSetting314;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PengaturanKonsumsiRumahTanggaTest314 extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);
    }

    public function test_tamu_tidak_dapat_mengakses_halaman_pengaturan_rumah_tangga(): void
    {
        $response = $this->get(route('rumah.index'));
        $response->assertRedirect(route('auth.masuk'));
    }

    public function test_pengguna_dapat_melihat_halaman_pengaturan_rumah_tangga(): void
    {
        $user = User314::factory()->create();

        $response = $this->actingAs($user)->get(route('rumah.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan Konsumsi Rumah Tangga');
        $response->assertSee('Jumlah Penghuni Rumah');
    }

    public function test_menolak_ketika_aturan_dilanggar_household_size_wajib(): void
    {
        $user = User314::factory()->create();

        $response = $this->actingAs($user)->put(route('rumah.upsert'), [
            'household_size' => '',
            'power_watt' => 1300,
            'vehicle_ownership' => 'motor',
        ]);

        $response->assertSessionHasErrors('household_size');
    }

    public function test_menolak_ketika_aturan_dilanggar_power_watt_wajib(): void
    {
        $user = User314::factory()->create();

        $response = $this->actingAs($user)->put(route('rumah.upsert'), [
            'household_size' => 4,
            'power_watt' => '',
            'vehicle_ownership' => 'motor',
        ]);

        $response->assertSessionHasErrors('power_watt');
    }

    public function test_menolak_ketika_aturan_dilanggar_vehicle_ownership_wajib(): void
    {
        $user = User314::factory()->create();

        $response = $this->actingAs($user)->put(route('rumah.upsert'), [
            'household_size' => 4,
            'power_watt' => 1300,
            'vehicle_ownership' => '',
        ]);

        $response->assertSessionHasErrors('vehicle_ownership');
    }

    public function test_nilai_di_luar_rentang_ditolak_dengan_pesan_bahasa_indonesia(): void
    {
        $user = User314::factory()->create();

        // household_size > 20
        $response = $this->actingAs($user)->put(route('rumah.upsert'), [
            'household_size' => 25,
            'power_watt' => 1300,
            'vehicle_ownership' => 'motor',
        ]);
        $response->assertSessionHasErrors(['household_size' => 'Jumlah penghuni antara 1 sampai 20 orang.']);

        // power_watt < 450
        $response2 = $this->actingAs($user)->put(route('rumah.upsert'), [
            'household_size' => 4,
            'power_watt' => 200,
            'vehicle_ownership' => 'motor',
        ]);
        $response2->assertSessionHasErrors(['power_watt' => 'Daya listrik antara 450 sampai 50000 watt.']);

        // vehicle_ownership bukan pilihan valid
        $response3 = $this->actingAs($user)->put(route('rumah.upsert'), [
            'household_size' => 4,
            'power_watt' => 1300,
            'vehicle_ownership' => 'pesawat',
        ]);
        $response3->assertSessionHasErrors('vehicle_ownership');
    }

    public function test_memastikan_satu_pengguna_tidak_mungkin_punya_dua_baris_user_settings(): void
    {
        $user = User314::factory()->create();

        // Panggilan pertama
        $this->actingAs($user)->put(route('rumah.upsert'), [
            'household_size' => 4,
            'power_watt' => 1300,
            'vehicle_ownership' => 'motor',
        ]);

        // Panggilan kedua (update data yang sama)
        $this->actingAs($user)->put(route('rumah.upsert'), [
            'household_size' => 5,
            'power_watt' => 2200,
            'vehicle_ownership' => 'mobil',
        ]);

        // Harus tetap ada 1 baris untuk pengguna ini
        $this->assertSame(1, UserSetting314::where('user_id', $user->id)->count());

        $setting = UserSetting314::where('user_id', $user->id)->first();
        $this->assertSame(5, $setting->household_size);
        $this->assertSame(2200, $setting->power_watt);
        $this->assertSame('mobil', $setting->vehicle_ownership);
    }

    public function test_memastikan_modul_pencatatan_dapat_membaca_angka_lewat_relasi_user_setting(): void
    {
        $user = User314::factory()->create();

        $this->actingAs($user)->put(route('rumah.upsert'), [
            'household_size' => 3,
            'power_watt' => 900,
            'vehicle_ownership' => 'keduanya',
        ]);

        $user->refresh();

        $this->assertNotNull($user->setting);
        $this->assertSame(3, $user->setting->household_size);
        $this->assertSame(900, $user->setting->power_watt);
        $this->assertSame('keduanya', $user->setting->vehicle_ownership);

        // Alias userSetting juga dapat dibaca
        $this->assertSame(3, $user->userSetting->household_size);
    }

    public function test_pengguna_dapat_menghapus_data_rumah_tangga(): void
    {
        $user = User314::factory()->create();

        UserSetting314::create([
            'user_id' => $user->id,
            'household_size' => 4,
            'power_watt' => 1300,
            'vehicle_ownership' => 'motor',
        ]);

        $response = $this->actingAs($user)->delete(route('rumah.destroy'));

        $response->assertRedirect(route('rumah.index'));
        $response->assertSessionHas('sukses');

        $this->assertSame(0, UserSetting314::where('user_id', $user->id)->count());
    }

    public function test_otorisasi_pengguna_tidak_dapat_mengubah_setting_pengguna_lain(): void
    {
        $userA = User314::factory()->create();
        $userB = User314::factory()->create();

        $settingB = UserSetting314::create([
            'user_id' => $userB->id,
            'household_size' => 2,
            'power_watt' => 900,
            'vehicle_ownership' => 'tidak ada',
        ]);

        $this->assertFalse($userA->can('update', $settingB));
        $this->assertFalse($userA->can('delete', $settingB));
    }
}
