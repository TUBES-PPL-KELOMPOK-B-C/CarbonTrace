<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SiapkanData;
use Tests\DuskTestCase;

// KF-21 Kerangka aplikasi (Alvin-111): sidebar drill-down, avatar, lonceng, akun antartab, layar sempit
class KerangkaAplikasiTest extends DuskTestCase
{
    use DatabaseMigrations, SiapkanData;

    public function test_aktivitas_dan_target_terbuka_bawaan_dan_pilihan_tutup_diingat(): void
    {
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->loginAs($pengguna)->visit('/dashboard')
                ->assertAttribute('details[data-kelompok="aktivitas"]', 'open', 'true')
                ->assertAttribute('details[data-kelompok="target"]', 'open', 'true')
                ->assertAttributeMissing('details[data-kelompok="profil"]', 'open')
                ->click('details[data-kelompok="aktivitas"] > summary')
                ->assertAttributeMissing('details[data-kelompok="aktivitas"]', 'open')
                ->visit('/lencana')
                ->assertAttributeMissing('details[data-kelompok="aktivitas"]', 'open')
                ->assertAttribute('details[data-kelompok="target"]', 'open', 'true')
                // Kelompok yang memuat halaman aktif selalu terbuka
                ->visit('/aktivitas')
                ->assertAttribute('details[data-kelompok="aktivitas"]', 'open', 'true');
        });
    }

    public function test_avatar_di_kanan_atas_membuka_profil(): void
    {
        $pengguna = $this->buatPengguna(['name' => 'Sari Avatar']);

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->loginAs($pengguna)->visit('/dashboard')
                ->assertSeeIn('.ap-pengguna', 'Sari')
                ->click('a.ap-pengguna')
                ->assertPathIs('/profil');
        });
    }

    public function test_lonceng_menampilkan_hasil_simpan_lalu_ditandai_dibaca(): void
    {
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->loginAs($pengguna)->visit('/pengingat')
                ->assertMissing('.ap-lonceng__jumlah')
                ->press('Tambah Pengingat')
                ->waitFor('#panel-pengingat-baru-jam')
                ->clear('#panel-pengingat-baru-jam')
                ->type('#panel-pengingat-baru-jam', '20:00')
                ->press('Setel Pengingat')
                ->waitForText('Pengingat disetel pukul 20.00.')
                ->assertSeeIn('.ap-lonceng__jumlah', '1')
                ->click('#ct-toast button') // toast menutupi lonceng selama 4 detik
                ->waitUntilMissing('#ct-toast')
                // Panel dibuka lewat tombol, ditutup lewat Esc
                ->assertMissing('#ap-notif-panel')
                ->click('[data-notif-tombol]')
                ->waitFor('#ap-notif-panel')
                ->assertSeeIn('#ap-notif-panel', 'Pengingat disetel pukul 20.00.')
                ->tap(fn (Browser $b) => $b->driver->getKeyboard()->pressKey(\Facebook\WebDriver\WebDriverKeys::ESCAPE))
                ->waitUntilMissing('#ap-notif-panel')
                // Membuka notifikasi menandainya dibaca
                ->click('[data-notif-tombol]')
                ->waitFor('#ap-notif-panel')
                ->click('.ap-notif__butir')
                ->waitForLocation('/pengingat')
                ->assertMissing('.ap-lonceng__jumlah');
        });
    }

    public function test_tab_lain_diberi_tahu_saat_akun_keluar(): void
    {
        $pengguna = $this->buatPengguna(['name' => 'Tari Tab']);

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->loginAs($pengguna)->visit('/dashboard');
            $tabPertama = $browser->driver->getWindowHandle();

            $browser->driver->switchTo()->newWindow();
            $browser->visit('/dashboard')->press('Keluar')->waitForLocation('/');

            $browser->driver->switchTo()->window($tabPertama);
            $browser->waitFor('.ap-akun-berganti')
                ->assertSeeIn('.ap-akun-berganti', 'Akun Tari sudah keluar di tab lain.')
                ->assertSeeIn('.ap-akun-berganti', 'InPrivate');
        });
    }

    public function test_layar_sempit_memakai_menu_bawah(): void
    {
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->resize(400, 860)->loginAs($pengguna)->visit('/dashboard')
                ->assertMissing('.ap-sisi')
                ->assertVisible('.ap-bawah')
                ->click('.ap-bawah a[href$="/aktivitas/buat"]')
                ->assertPathIs('/aktivitas/buat');
            $browser->resize(1440, 1000);
        });
    }
}
