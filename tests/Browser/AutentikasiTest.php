<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SiapkanData;
use Tests\DuskTestCase;

// KF-01 Registrasi dan Login (Alvin-111)
class AutentikasiTest extends DuskTestCase
{
    use DatabaseMigrations, SiapkanData;

    public function test_daftar_lalu_kembali_ke_beranda_dan_email_terisi_saat_masuk(): void
    {
        $this->siapkanPeran();

        $this->browse(function (Browser $browser) {
            $browser->visit('/daftar')
                ->type('#name', 'Budi Dusk')
                ->type('#email', 'budi.dusk@contoh.com')
                ->type('#password', self::SANDI)
                ->type('#password_confirmation', self::SANDI)
                ->check('setuju')
                ->press('Buat Akun')
                ->waitForLocation('/')
                ->waitForText('Akun berhasil dibuat. Silakan masuk untuk mulai mencatat.')
                ->visit('/masuk')
                ->assertInputValue('#email', 'budi.dusk@contoh.com');
        });
    }

    public function test_pilihan_masuk_sebagai_harus_cocok_dengan_peran_akun(): void
    {
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->visit('/masuk')
                ->assertRadioSelected('sebagai', 'pengguna')
                ->click('label.lp-sebagai__opsi:nth-child(2)')
                ->assertDontSee('Daftar gratis') // pendaftaran hanya untuk pengguna
                ->type('#email', $pengguna->email)
                ->type('#password', self::SANDI)
                ->press('Masuk')
                ->waitForText('Akun ini bukan akun administrator.')
                ->assertPathIs('/masuk')
                ->assertRadioSelected('sebagai', 'administrator')
                ->click('label.lp-sebagai__opsi:nth-child(1)')
                ->assertSee('Daftar gratis')
                ->type('#password', self::SANDI)
                ->press('Masuk')
                ->waitForLocation('/dashboard')
                ->assertSee('Halo, '.str($pengguna->name)->before(' '));
        });
    }

    public function test_administrator_masuk_lewat_pilihan_administrator(): void
    {
        $admin = $this->buatAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/masuk')
                ->type('#email', $admin->email)
                ->type('#password', self::SANDI)
                ->press('Masuk')
                ->waitForText('Ini akun administrator.')
                ->click('label.lp-sebagai__opsi:nth-child(2)')
                ->type('#password', self::SANDI)
                ->press('Masuk')
                ->waitForLocation('/dashboard')
                ->assertSee('Manajemen Pengguna');
        });
    }

    public function test_tombol_mata_memperlihatkan_dan_menyembunyikan_kata_sandi(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/masuk')
                ->type('#password', 'rahasia')
                ->assertAttribute('#password', 'type', 'password')
                ->click('[data-lihat-sandi="password"]')
                ->assertAttribute('#password', 'type', 'text')
                ->click('[data-lihat-sandi="password"]')
                ->assertAttribute('#password', 'type', 'password');
        });
    }

    // Server tes memakai MAIL_MAILER=log: emailnya tercatat di storage/logs, tidak terkirim sungguhan.
    public function test_lupa_sandi_mengirim_tautan_dan_menolak_email_tak_terdaftar(): void
    {
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->visit('/lupa-sandi')
                ->type('#email', 'tidak.ada@contoh.com')
                ->press('Kirim Tautan')
                ->waitForText('Email tersebut belum terdaftar di CarbonTrace.')
                ->type('#email', $pengguna->email)
                ->press('Kirim Tautan')
                ->waitForText('Tautan untuk membuat kata sandi baru telah dikirim ke email Anda.');
        });
    }
}
