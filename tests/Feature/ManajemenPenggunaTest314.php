<?php

namespace Tests\Feature;

use App\Models\User314;
use Database\Seeders\RoleSeeder314;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManajemenPenggunaTest314 extends TestCase
{
    use RefreshDatabase;

    protected User314 $admin;
    protected User314 $penggunaBiasa;
    protected int $adminRoleId;
    protected int $userRoleId;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);

        $this->adminRoleId = $adminRole->id;
        $this->userRoleId = $userRole->id;

        $this->admin = User314::factory()->create([
            'role_id' => $this->adminRoleId,
            'is_active' => true,
        ]);
        $this->admin->assignRole('administrator');

        $this->penggunaBiasa = User314::factory()->create([
            'role_id' => $this->userRoleId,
            'is_active' => true,
        ]);
        $this->penggunaBiasa->assignRole('pengguna');
    }

    /**
     * Kriteria Selesai 1: Pengguna biasa menerima galat 403 saat membuka rute admin.
     */
    public function test_pengguna_biasa_menerima_galat_403_saat_membuka_rute_admin(): void
    {
        $response = $this->actingAs($this->penggunaBiasa)->get('/admin/pengguna');

        $response->assertStatus(403);
    }

    /**
     * Administrator dapat membuka rute admin pengguna.
     */
    public function test_administrator_dapat_mengakses_panel_manajemen_pengguna(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/pengguna');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
    }

    /**
     * Kriteria Selesai 2: Administrator terakhir tidak dapat dinonaktifkan.
     */
    public function test_administrator_terakhir_tidak_dapat_dinonaktifkan(): void
    {
        // $this->admin adalah satu-satunya administrator aktif
        $this->assertEquals(1, User314::where('role_id', $this->adminRoleId)->where('is_active', true)->count());

        // Buat admin kedua untuk melakukan request
        $admin2 = User314::factory()->create([
            'role_id' => $this->adminRoleId,
            'is_active' => true,
        ]);
        $admin2->assignRole('administrator');

        // Nonaktifkan admin pertama
        $this->actingAs($admin2)->patch("/admin/pengguna/{$this->admin->id}/status");
        $this->assertFalse((bool) $this->admin->fresh()->is_active);

        // Sekarang hanya ada 1 admin aktif yaitu admin2. Coba nonaktifkan admin2 (terakhir) oleh dirinya sendiri atau coba nonaktifkan admin terakhir
        $response = $this->actingAs($admin2)->patch("/admin/pengguna/{$admin2->id}/status");
        $response->assertSessionHasErrors('status');
        $this->assertTrue((bool) $admin2->fresh()->is_active);
    }

    /**
     * Kriteria Selesai 3: Seeder menghasilkan dua peran dan satu akun administrator.
     */
    public function test_seeder_menghasilkan_dua_peran_dan_satu_akun_administrator(): void
    {
        $this->seed(RoleSeeder314::class);

        $this->assertDatabaseHas('roles', ['name' => 'administrator']);
        $this->assertDatabaseHas('roles', ['name' => 'pengguna']);
        $this->assertDatabaseHas('users', ['email' => 'admin@carbontrace.id']);
    }

    /**
     * Kriteria Selesai 4: Perubahan peran langsung berlaku tanpa perlu masuk ulang.
     */
    public function test_perubahan_peran_langsung_berlaku_tanpa_masuk_ulang(): void
    {
        $targetUser = User314::factory()->create([
            'role_id' => $this->userRoleId,
            'is_active' => true,
        ]);
        $targetUser->assignRole('pengguna');

        // Sebelum diubah, targetUser mendapat 403
        $this->actingAs($targetUser)->get('/admin/pengguna')->assertStatus(403);

        // Administrator mengubah peran targetUser menjadi administrator
        $response = $this->actingAs($this->admin)->patch("/admin/pengguna/{$targetUser->id}/peran", [
            'role_id' => $this->adminRoleId,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals($this->adminRoleId, $targetUser->fresh()->role_id);

        // Setelah diubah, targetUser langsung dapat mengakses rute admin tanpa perlu login ulang
        $this->actingAs($targetUser->fresh())->get('/admin/pengguna')->assertStatus(200);
    }

    /**
     * Administrator tidak boleh menurunkan perannya sendiri.
     */
    public function test_administrator_tidak_boleh_menurunkan_perannya_sendiri(): void
    {
        $response = $this->actingAs($this->admin)->patch("/admin/pengguna/{$this->admin->id}/peran", [
            'role_id' => $this->userRoleId,
        ]);

        $response->assertSessionHasErrors('peran');
        $this->assertEquals($this->adminRoleId, $this->admin->fresh()->role_id);
    }

    /**
     * Administrator dapat menambah pengguna baru dari panel.
     */
    public function test_administrator_dapat_menambah_pengguna_baru(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/pengguna', [
            'name' => 'Pengguna Baru Dari Admin',
            'email' => 'baru@carbontrace.id',
            'password' => 'Password123!',
            'role_id' => $this->userRoleId,
        ]);

        $response->assertRedirect('/admin/pengguna');
        $response->assertSessionHas('sukses');
        $this->assertDatabaseHas('users', [
            'email' => 'baru@carbontrace.id',
            'name' => 'Pengguna Baru Dari Admin',
        ]);
    }
}
