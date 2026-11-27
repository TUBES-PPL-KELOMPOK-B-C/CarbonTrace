<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SiapkanData;
use Tests\DuskTestCase;

// KF-25 Kuis Awal dan Hasil Kuis Awal untuk admin (Alvin-111)
class KuisAwalTest extends DuskTestCase
{
    use DatabaseMigrations, SiapkanData;

    public function test_pengguna_baru_diarahkan_ke_kuis_lalu_mendapat_garis_dasar(): void
    {
        $this->siapkanSoalKuis();
        $pengguna = $this->buatPengguna(['name' => 'Kiki Kuis'], lewatiKuis: false);

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->visit('/masuk')
                ->type('#email', $pengguna->email)
                ->type('#password', self::SANDI)
                ->press('Masuk')
                ->waitForLocation('/kuis-awal')
                ->assertSee('Soal 1 dari 2')
                ->radio('pilihan', '1')
                ->press('Lanjut')
                ->waitForText('Soal 2 dari 2')
                // Kembali ke soal 1: jawaban tadi masih terpilih
                ->clickLink('Kembali')
                ->waitForText('Soal 1 dari 2')
                ->assertRadioSelected('pilihan', '1')
                ->press('Lanjut')
                ->waitForText('Soal 2 dari 2')
                ->radio('pilihan', '1')
                ->press('Lihat Garis Dasar')
                ->waitForLocation('/kuis-awal/hasil')
                ->assertSeeIn('.ka-hasil__angka', '12,00')
                ->clickLink('Ke Dashboard')
                ->waitForText('Garis dasar kuis awal 12,00 kg per hari')
                ->assertDontSee('Isi kuis awal untuk mengetahui garis dasar emisimu');
        });
    }

    public function test_melewati_kuis_lalu_dashboard_tetap_menawarkan_kuis(): void
    {
        $this->siapkanSoalKuis();
        $pengguna = $this->buatPengguna(lewatiKuis: false);

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->loginAs($pengguna)->visit('/kuis-awal')
                ->press('Lewati kuis untuk sekarang')
                ->waitForLocation('/dashboard')
                ->assertSee('Isi kuis awal untuk mengetahui garis dasar emisimu')
                ->clickLink('Mulai Kuis')
                ->waitForText('Soal 1 dari 2');
        });
    }

    public function test_admin_melihat_sebaran_jawaban_tanpa_ikut_dihitung(): void
    {
        $this->siapkanSoalKuis();
        $pengguna = $this->buatPengguna(['name' => 'Peserta Kuis'], lewatiKuis: false);
        $admin = $this->buatAdmin();

        $this->browse(function (Browser $browser) use ($pengguna, $admin) {
            $browser->loginAs($pengguna)->visit('/kuis-awal')
                ->radio('pilihan', '1')->press('Lanjut')->waitForText('Soal 2 dari 2')
                ->radio('pilihan', '0')->press('Lihat Garis Dasar')->waitForLocation('/kuis-awal/hasil');

            $browser->loginAs($admin)->visit('/admin/kuis-awal')
                ->assertSee('Sebaran jawaban per soal')
                ->assertSee('Peserta Kuis')
                ->assertSeeIn('.ka-angka article:nth-child(2)', '1')
                // Kabar ke lonceng administrator
                ->click('[data-notif-tombol]')
                ->waitFor('#ap-notif-panel')
                ->assertSeeIn('#ap-notif-panel', 'Peserta Kuis menyelesaikan kuis awal.');
        });
    }
}
