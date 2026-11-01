<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class LupaSandiTest111 extends TestCase
{
    use RefreshDatabase;

    private const PESAN_TERKIRIM = 'Tautan untuk membuat kata sandi baru telah dikirim ke email Anda. Silakan periksa kotak masuk dan folder spam.';

    public function test_halaman_lupa_sandi_bisa_dibuka_dan_ditautkan_dari_halaman_masuk(): void
    {
        $this->get(route('auth.masuk'))->assertSee('Lupa kata sandi?')->assertSee(route('auth.lupa'));
        $this->get(route('auth.lupa'))->assertOk()->assertSee('Lupa Kata Sandi');
    }

    public function test_tautan_pemulihan_dikirim_ke_email_terdaftar_dalam_bahasa_indonesia(): void
    {
        Notification::fake();
        $pengguna = User::factory()->create();

        $this->from(route('auth.lupa'))
            ->post(route('auth.kirim'), ['email' => $pengguna->email])
            ->assertRedirect(route('auth.lupa'))
            ->assertSessionHas('status', self::PESAN_TERKIRIM);

        Notification::assertSentTo($pengguna, ResetPassword::class, function (ResetPassword $notifikasi) use ($pengguna) {
            $surat = $notifikasi->toMail($pengguna);

            return $surat->subject === 'Atur Ulang Kata Sandi CarbonTrace'
                && str_contains($surat->actionUrl, '/atur-ulang-sandi/'.$notifikasi->token);
        });
    }

    public function test_email_yang_belum_terdaftar_diberi_peringatan(): void
    {
        Notification::fake();

        $this->from(route('auth.lupa'))
            ->post(route('auth.kirim'), ['email' => 'belum@terdaftar.id'])
            ->assertRedirect(route('auth.lupa'))
            ->assertSessionHasErrors(['email' => 'Email tersebut belum terdaftar di CarbonTrace. Silakan periksa kembali alamat email Anda atau daftar terlebih dahulu.']);

        $this->postJson(route('auth.api.lupa'), ['email' => 'belum@terdaftar.id'])
            ->assertNotFound()
            ->assertJsonPath('pesan', 'Email tersebut belum terdaftar di CarbonTrace. Silakan periksa kembali alamat email Anda atau daftar terlebih dahulu.');

        Notification::assertNothingSent();
    }

    public function test_akun_nonaktif_tidak_dikirimi_tautan(): void
    {
        Notification::fake();
        $nonaktif = User::factory()->create(['is_active' => false]);

        $this->from(route('auth.lupa'))
            ->post(route('auth.kirim'), ['email' => $nonaktif->email])
            ->assertSessionHasErrors(['email' => 'Akun dengan email tersebut telah dinonaktifkan. Silakan hubungi administrator.']);

        Notification::assertNothingSent();
    }

    public function test_meminta_tautan_lagi_kurang_dari_semenit_ditolak_dengan_sopan(): void
    {
        Notification::fake();
        $pengguna = User::factory()->create();

        $this->post(route('auth.kirim'), ['email' => $pengguna->email])->assertSessionHas('status');
        $this->from(route('auth.lupa'))
            ->post(route('auth.kirim'), ['email' => $pengguna->email])
            ->assertSessionHasErrors(['email' => 'Tautan pemulihan baru saja dikirim. Silakan tunggu satu menit sebelum meminta tautan baru.']);

        Notification::assertSentToTimes($pengguna, ResetPassword::class, 1);
    }

    public function test_kata_sandi_baru_tersimpan_lalu_bisa_dipakai_masuk(): void
    {
        $pengguna = User::factory()->create(['password' => 'sandi-lama-123']);
        $token = Password::createToken($pengguna);

        $this->get(route('auth.atur.form', ['token' => $token, 'email' => $pengguna->email]))
            ->assertOk()
            ->assertSee('Buat Kata Sandi Baru')
            ->assertSee($pengguna->email);

        $this->post(route('auth.atur'), [
            'token' => $token,
            'email' => $pengguna->email,
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'sandi-baru-123',
        ])->assertRedirect(route('auth.masuk'))
            ->assertSessionHas('status', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru Anda.');

        $this->assertTrue(Hash::check('sandi-baru-123', $pengguna->fresh()->password));
        $this->assertGuest();
    }

    public function test_tautan_yang_sudah_dipakai_ditolak_saat_dipakai_kedua_kali(): void
    {
        $pengguna = User::factory()->create();
        $token = Password::createToken($pengguna);
        $data = ['token' => $token, 'email' => $pengguna->email, 'password' => 'sandi-baru-123', 'password_confirmation' => 'sandi-baru-123'];

        $this->post(route('auth.atur'), $data)->assertRedirect(route('auth.masuk'));

        $this->from(route('auth.atur.form', ['token' => $token]))
            ->post(route('auth.atur'), ['password' => 'sandi-lain-123', 'password_confirmation' => 'sandi-lain-123'] + $data)
            ->assertRedirect(route('auth.atur.form', ['token' => $token]))
            ->assertSessionHas('galat_atur', 'Tautan pemulihan tidak valid atau telah kedaluwarsa. Silakan minta tautan baru.');

        $this->assertTrue(Hash::check('sandi-baru-123', $pengguna->fresh()->password));
    }

    public function test_sandi_baru_tidak_valid_ditolak_per_kolom(): void
    {
        $pengguna = User::factory()->create();

        $this->from(route('auth.atur.form', ['token' => 'apa-saja']))
            ->post(route('auth.atur'), ['token' => 'apa-saja', 'email' => $pengguna->email, 'password' => 'pendek', 'password_confirmation' => 'beda'])
            ->assertSessionHasErrors(['password' => 'Kata sandi minimal 8 karakter.']);
    }

    public function test_versi_json_kirim_tautan_dan_atur_ulang(): void
    {
        Notification::fake();
        $pengguna = User::factory()->create();

        $this->postJson(route('auth.api.lupa'), ['email' => $pengguna->email])
            ->assertOk()
            ->assertJson(['pesan' => self::PESAN_TERKIRIM]);
        Notification::assertSentTo($pengguna, ResetPassword::class);

        $this->postJson(route('auth.api.atur'), ['token' => 'salah', 'email' => $pengguna->email, 'password' => 'sandi-baru-123', 'password_confirmation' => 'sandi-baru-123'])
            ->assertUnprocessable()
            ->assertJson(['pesan' => 'Tautan pemulihan tidak valid atau telah kedaluwarsa. Silakan minta tautan baru.']);

        $this->postJson(route('auth.api.atur'), ['token' => Password::createToken($pengguna), 'email' => $pengguna->email, 'password' => 'sandi-baru-123', 'password_confirmation' => 'sandi-baru-123'])
            ->assertOk()
            ->assertJson(['pesan' => 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru Anda.']);
    }
}
