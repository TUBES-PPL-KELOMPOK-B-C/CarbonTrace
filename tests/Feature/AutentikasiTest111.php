<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutentikasiTest111 extends TestCase
{
    use RefreshDatabase;

    private function dataDaftar(array $ubah = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'email' => 'budi@contoh.com',
            'campus' => 'Telkom University',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'setuju' => '1',
        ], $ubah);
    }

    public function test_halaman_masuk_dan_daftar_bisa_dibuka_tamu(): void
    {
        $this->get(route('auth.masuk'))->assertOk()->assertSee('Masuk ke CarbonTrace');
        $this->get(route('auth.daftar'))->assertOk()->assertSee('Buat Akun CarbonTrace');
    }

    public function test_pendaftaran_membuat_akun_lalu_langsung_masuk(): void
    {
        $this->post(route('auth.simpan'), $this->dataDaftar())
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'budi@contoh.com',
            'campus' => 'Telkom University',
            'is_active' => true,
        ]);
    }

    public function test_pendaftaran_ditolak_jika_data_tidak_valid(): void
    {
        User::factory()->create(['email' => 'sudah@ada.id']);

        $this->from(route('auth.daftar'))
            ->post(route('auth.simpan'), $this->dataDaftar([
                'email' => 'sudah@ada.id',
                'password_confirmation' => 'beda12345',
                'setuju' => null,
            ]))
            ->assertRedirect(route('auth.daftar'))
            ->assertSessionHasErrors(['email', 'password', 'setuju']);

        $this->assertGuest();
    }

    public function test_masuk_dengan_kredensial_benar(): void
    {
        $pengguna = User::factory()->create(['password' => 'rahasia123']);

        $this->post(route('auth.proses'), ['email' => $pengguna->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_sandi_salah_memberi_satu_pesan_umum(): void
    {
        $pengguna = User::factory()->create(['password' => 'rahasia123']);

        $this->from(route('auth.masuk'))
            ->post(route('auth.proses'), ['email' => $pengguna->email, 'password' => 'salah-sandi'])
            ->assertRedirect(route('auth.masuk'))
            ->assertSessionHas('galat_masuk', 'Email atau kata sandi salah.');

        $this->assertGuest();
    }

    public function test_email_yang_belum_terdaftar_mendapat_pesan_yang_sama(): void
    {
        $this->from(route('auth.masuk'))
            ->post(route('auth.proses'), ['email' => 'belum@terdaftar.id', 'password' => 'apa-saja'])
            ->assertRedirect(route('auth.masuk'))
            ->assertSessionHas('galat_masuk', 'Email atau kata sandi salah.');

        $this->assertGuest();
    }

    public function test_lima_kali_salah_sandi_dikunci_satu_menit(): void
    {
        $pengguna = User::factory()->create(['password' => 'rahasia123']);

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('auth.masuk'))
                ->post(route('auth.proses'), ['email' => $pengguna->email, 'password' => 'salah-sandi'])
                ->assertSessionHas('galat_masuk', 'Email atau kata sandi salah.');
        }

        // percobaan ke-6, bahkan dengan sandi yang benar, ditolak dengan pesan sisa waktu
        $this->from(route('auth.masuk'))
            ->post(route('auth.proses'), ['email' => $pengguna->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('auth.masuk'))
            ->assertSessionHas('galat_masuk', fn ($pesan) => str_starts_with($pesan, 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam '));
        $this->assertGuest();

        // email lain dari IP yang sama tidak ikut terkunci
        $lain = User::factory()->create(['password' => 'rahasia123']);
        $this->post(route('auth.proses'), ['email' => $lain->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($lain);
        $this->post(route('auth.keluar'));

        // setelah satu menit berlalu, bisa masuk lagi
        $this->travel(61)->seconds();
        $this->post(route('auth.proses'), ['email' => $pengguna->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_berhasil_masuk_mereset_hitungan_salah(): void
    {
        $pengguna = User::factory()->create(['password' => 'rahasia123']);
        $salah = fn () => $this->post(route('auth.proses'), ['email' => $pengguna->email, 'password' => 'salah-sandi']);

        for ($i = 0; $i < 4; $i++) {
            $salah();
        }
        $this->post(route('auth.proses'), ['email' => $pengguna->email, 'password' => 'rahasia123']);
        $this->post(route('auth.keluar'));

        // hitungan mulai dari nol lagi: salah sekali tidak langsung terkunci
        $salah()->assertSessionHas('galat_masuk', 'Email atau kata sandi salah.');
    }

    public function test_api_masuk_setelah_lima_kali_salah_memberi_429(): void
    {
        $pengguna = User::factory()->create(['password' => 'rahasia123']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('auth.api.masuk'), ['email' => $pengguna->email, 'password' => 'salah-sandi'])->assertUnauthorized();
        }

        $this->postJson(route('auth.api.masuk'), ['email' => $pengguna->email, 'password' => 'rahasia123'])
            ->assertStatus(429)
            ->assertJsonPath('pesan', fn ($pesan) => str_starts_with($pesan, 'Terlalu banyak percobaan masuk.'));
    }

    public function test_akun_nonaktif_ditolak_dengan_pesan_khusus(): void
    {
        $pengguna = User::factory()->create(['password' => 'rahasia123', 'is_active' => false]);

        $this->from(route('auth.masuk'))
            ->post(route('auth.proses'), ['email' => $pengguna->email, 'password' => 'rahasia123'])
            ->assertSessionHas('galat_masuk', 'Akun ini dinonaktifkan. Hubungi administrator.');

        $this->assertGuest();
    }

    public function test_keluar_mengakhiri_sesi(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('auth.keluar'))
            ->assertRedirect(route('beranda'));

        $this->assertGuest();
    }

    public function test_tamu_di_halaman_ber_auth_dipantulkan_ke_halaman_masuk(): void
    {
        $this->get(route('artikel-edukasi.index'))->assertRedirect(route('auth.masuk'));
    }

    public function test_pengguna_yang_sudah_masuk_tidak_bisa_membuka_halaman_masuk(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('auth.masuk'))
            ->assertRedirect(route('dashboard.index'));
    }

    // ---- Versi JSON ----

    public function test_api_daftar_mengembalikan_akun_baru_tanpa_kata_sandi(): void
    {
        $this->postJson(route('auth.api.daftar'), $this->dataDaftar())
            ->assertCreated()
            ->assertJsonPath('pesan', 'Akun berhasil dibuat.')
            ->assertJsonPath('pengguna.email', 'budi@contoh.com')
            ->assertJsonPath('pengguna.campus', 'Telkom University')
            ->assertJsonMissingPath('pengguna.password');

        $this->assertAuthenticated();
    }

    public function test_api_daftar_tidak_valid_memberi_galat_per_kolom(): void
    {
        $this->postJson(route('auth.api.daftar'), $this->dataDaftar(['email' => 'bukan-email', 'setuju' => null]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => 'Format email belum benar.', 'setuju']);
    }

    public function test_api_masuk_berhasil_lalu_saya_lalu_keluar(): void
    {
        $pengguna = User::factory()->create(['password' => 'rahasia123']);

        $this->postJson(route('auth.api.masuk'), ['email' => $pengguna->email, 'password' => 'rahasia123'])
            ->assertOk()
            ->assertJsonPath('pengguna.id', $pengguna->id);

        $this->getJson(route('auth.api.saya'))->assertOk()->assertJsonPath('pengguna.email', $pengguna->email);

        $this->postJson(route('auth.api.keluar'))->assertOk()->assertJson(['pesan' => 'Berhasil keluar.']);
        $this->assertGuest();
    }

    public function test_api_masuk_salah_sandi_dan_akun_nonaktif(): void
    {
        $aktif = User::factory()->create(['password' => 'rahasia123']);
        $nonaktif = User::factory()->create(['password' => 'rahasia123', 'is_active' => false]);

        $this->postJson(route('auth.api.masuk'), ['email' => $aktif->email, 'password' => 'salah-sandi'])
            ->assertUnauthorized()
            ->assertJson(['pesan' => 'Email atau kata sandi salah.']);

        $this->postJson(route('auth.api.masuk'), ['email' => $nonaktif->email, 'password' => 'rahasia123'])
            ->assertForbidden()
            ->assertJson(['pesan' => 'Akun ini dinonaktifkan. Hubungi administrator.']);

        $this->assertGuest();
    }

    public function test_api_menolak_badan_selain_json(): void
    {
        $pengguna = User::factory()->create(['password' => 'rahasia123']);

        $this->post(route('auth.api.masuk'), ['email' => $pengguna->email, 'password' => 'rahasia123'])
            ->assertStatus(415);

        $this->assertGuest();
    }

    public function test_api_saya_tanpa_masuk_memberi_401_berbahasa_indonesia(): void
    {
        $this->getJson(route('auth.api.saya'))
            ->assertUnauthorized()
            ->assertJson(['pesan' => 'Kamu belum masuk. Masuk dulu lewat /api/masuk.']);
    }

    public function test_api_masuk_ditolak_bila_sudah_masuk(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('auth.api.masuk'), ['email' => 'lain@contoh.com', 'password' => 'rahasia123'])
            ->assertStatus(409);
    }
}
