<?php

namespace Tests\Feature;

use App\Models\User314;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// Perbaikan bersama di branch Solving_Project_teams (Alvin-111)
class PerbaikanTimTest111 extends TestCase
{
    use RefreshDatabase;

    public function test_rute_masuk_tanpa_sandi_sudah_tidak_ada(): void
    {
        foreach (['/dev-login-admin', '/dev-login-pengguna', '/dev-logout'] as $jalur) {
            $this->get($jalur)->assertNotFound();
        }
        $this->assertGuest();
    }

    public function test_administrator_tidak_ikut_diperingkat(): void
    {
        Role::findOrCreate('administrator', 'web');
        Role::findOrCreate('pengguna', 'web');

        $admin = User314::factory()->create(['name' => 'Admin Tersembunyi', 'is_active' => true]);
        $admin->assignRole('administrator');
        $peserta = User314::factory()->create(['name' => 'Peserta Biasa', 'is_active' => true]);
        $peserta->assignRole('pengguna');

        $this->actingAs($peserta)->get(route('peringkat.index'))->assertOk()
            ->assertSee('Peserta Biasa')
            ->assertDontSee('Admin Tersembunyi');
    }
}
