<?php

namespace Tests\Feature;

use App\Models\User314;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// Aturan satu administrator di Manajemen Pengguna (KF-04): administrator hanya satu akun,
// pengguna biasa tidak dibatasi. Peran administrator hanya berpindah lewat serah terima.
class SatuAdministratorTest111 extends TestCase
{
    use RefreshDatabase;

    private User314 $admin;

    private Role $peranAdmin;

    private Role $peranPengguna;

    protected function setUp(): void
    {
        parent::setUp();
        $this->peranAdmin = Role::findOrCreate('administrator', 'web');
        $this->peranPengguna = Role::findOrCreate('pengguna', 'web');

        $this->admin = $this->akun($this->peranAdmin);
    }

    private function akun(Role $peran): User314
    {
        $akun = User314::factory()->create(['role_id' => $peran->id, 'is_active' => true]);
        $akun->assignRole($peran->name);

        return $akun;
    }

    private function jumlahAdmin(): int
    {
        return User314::where('role_id', $this->peranAdmin->id)->count();
    }

    public function test_menjadikan_pengguna_administrator_adalah_serah_terima(): void
    {
        $penerima = $this->akun($this->peranPengguna);

        $this->actingAs($this->admin)->patch(route('pengguna.peran', $penerima), ['role_id' => $this->peranAdmin->id])
            ->assertRedirect(route('dashboard.index'))
            ->assertSessionHas('sukses', "Peran administrator diserahkan kepada {$penerima->name}. Akunmu sekarang pengguna biasa.");

        $this->assertSame(1, $this->jumlahAdmin());
        $this->assertTrue($penerima->fresh()->hasRole('administrator'));
        $this->assertFalse($this->admin->fresh()->hasRole('administrator'));
        $this->assertSame($this->peranPengguna->id, (int) $this->admin->fresh()->role_id);

        // Berlaku seketika: admin lama tidak bisa lagi membuka panel, penerima bisa.
        $this->actingAs($this->admin->fresh())->get(route('pengguna.index'))->assertForbidden();
        $this->actingAs($penerima->fresh())->get(route('pengguna.index'))->assertOk();
    }

    public function test_tambah_akun_baru_tidak_bisa_berperan_administrator(): void
    {
        $this->actingAs($this->admin)->post(route('pengguna.store'), [
            'name' => 'Calon Admin', 'email' => 'calon@carbontrace.id', 'password' => 'Password123!', 'role_id' => $this->peranAdmin->id,
        ])->assertSessionHasErrors(['role_id' => 'Akun administrator hanya boleh satu. Serahkan peran administrator lewat daftar pengguna.']);

        $this->assertDatabaseMissing('users', ['email' => 'calon@carbontrace.id']);
        $this->assertSame(1, $this->jumlahAdmin());
    }

    public function test_pengguna_biasa_boleh_banyak_dan_formulir_tambah_hanya_menawarkan_pengguna(): void
    {
        foreach (range(1, 3) as $i) {
            $this->actingAs($this->admin)->post(route('pengguna.store'), [
                'name' => "Pengguna {$i}", 'email' => "p{$i}@carbontrace.id", 'password' => 'Password123!', 'role_id' => $this->peranPengguna->id,
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(3, User314::where('email', 'like', 'p_@carbontrace.id')->count());
        $this->assertSame(1, $this->jumlahAdmin());

        $halaman = $this->get(route('pengguna.index'))->assertOk()->getContent();
        $formulirTambah = substr($halaman, strpos($halaman, 'id="tambah-peran"'), 400);
        $this->assertStringNotContainsString('Administrator', $formulirTambah);
    }

    public function test_administrator_satu_satunya_tetap_tidak_bisa_menurunkan_dirinya(): void
    {
        $this->actingAs($this->admin)->patch(route('pengguna.peran', $this->admin), ['role_id' => $this->peranPengguna->id])
            ->assertSessionHasErrors('peran');

        $this->assertSame(1, $this->jumlahAdmin());
    }
}
