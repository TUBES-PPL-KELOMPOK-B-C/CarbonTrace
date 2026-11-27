<?php

namespace Tests\Browser;

use App\Models\User314;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SiapkanData;
use Tests\DuskTestCase;

// PBI-01 Profil, PBI-02 Rumah Tangga, KF-04 Manajemen Pengguna, PBI-06 Bank Soal Kuis,
// PBI-13 Lencana, PBI-20 Papan Peringkat
class AkunDanAdminTest extends DuskTestCase
{
    use DatabaseMigrations, SiapkanData;

    public function test_unggah_foto_profil_tampil_di_avatar_bilah_atas(): void
    {
        $pengguna = $this->buatPengguna();
        // Objek berkas disimpan di variabel supaya berkas sementaranya tidak terhapus sebelum diunggah
        $berkas = UploadedFile::fake()->image('foto.jpg', 200, 200);
        $foto = $berkas->getPathname();

        $this->browse(function (Browser $browser) use ($pengguna, $foto) {
            $browser->loginAs($pengguna)->visit('/profil')
                ->assertMissing('img.ap-avatar--foto')
                ->attach('photo', $foto)
                ->press('Simpan Perubahan')
                ->waitFor('img.ap-avatar--foto');
        });

        $jalur = User314::find($pengguna->id)->photo;
        $this->assertNotNull($jalur);
        // Foto uji tidak dibiarkan menumpuk di storage/app/public/photos
        \Illuminate\Support\Facades\Storage::disk('public')->delete($jalur);
    }

    public function test_pengaturan_rumah_tangga_tersimpan(): void
    {
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->loginAs($pengguna)->visit('/pengaturan-rumah-tangga');
            $browser->script("const r=document.querySelector('#household_size');r.value=4;r.dispatchEvent(new Event('input',{bubbles:true}));r.dispatchEvent(new Event('change',{bubbles:true}));");
            $browser->assertSeeIn('#simulasi-orang', '4')
                ->click('#tombol-simpan-rumah')
                ->pause(1000);
        });

        $this->assertSame(4, (int) DB::table('user_settings')->where('user_id', $pengguna->id)->value('household_size'));
    }

    public function test_admin_menambah_pengguna_tanpa_pilihan_administrator(): void
    {
        $admin = $this->buatAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)->visit('/admin/pengguna')
                ->press('Tambah Akun Baru')
                ->waitFor('#modal-tambah-pengguna')
                ->assertDontSeeIn('#tambah-peran', 'Administrator')
                ->type('#tambah-nama', 'Pengguna Dari Admin')
                ->type('#tambah-email', 'dari.admin@contoh.com')
                ->type('#tambah-password', self::SANDI)
                ->press('Simpan Akun')
                ->waitForText('Akun pengguna berhasil ditambahkan.')
                ->assertSee('Pengguna Dari Admin');
        });
    }

    public function test_serah_terima_administrator_butuh_konfirmasi(): void
    {
        $admin = $this->buatAdmin();
        $penerima = $this->buatPengguna(['name' => 'Calon Admin']);

        $this->browse(function (Browser $browser) use ($admin, $penerima) {
            $pilihan = 'form[action$="/admin/pengguna/'.$penerima->id.'/peran"] select';

            $browser->loginAs($admin)->visit('/admin/pengguna')
                ->select($pilihan, '1')
                ->waitForDialog()
                ->assertDialogOpened('Serahkan peran administrator kepada Calon Admin? Akunmu akan menjadi pengguna biasa dan tidak bisa membuka panel admin lagi.')
                ->dismissDialog()
                ->assertSelected($pilihan, (string) $penerima->role_id)
                ->select($pilihan, '1')
                ->waitForDialog()
                ->acceptDialog()
                ->waitForLocation('/dashboard')
                ->waitForText('Peran administrator diserahkan kepada Calon Admin.')
                ->assertDontSee('Manajemen Pengguna');

            // Admin baru hasil serah terima memegang peran "administrator" saja dan tetap bisa
            // membuka semua halaman admin, termasuk Bank Soal Kuis milik Modul 2.
            $browser->loginAs($penerima->fresh())->visit('/admin/bank-kuis')
                ->assertSee('Bank Pertanyaan Kuis');
        });

        $this->assertTrue($penerima->fresh()->hasRole('administrator'));
        $this->assertFalse($admin->fresh()->hasRole('administrator'));
    }

    public function test_admin_membuat_soal_kuis_dengan_pilihan_dinamis(): void
    {
        $admin = $this->buatAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)->visit('/admin/bank-kuis/buat')
                ->type('#input-pertanyaan', 'Seberapa sering kamu naik ojek online setiap minggu?')
                ->press('Tambah Pilihan')
                ->assertPresent('input[name="options[2][label]"]')
                ->type('input[name="options[0][label]"]', 'Tidak pernah')
                ->type('input[name="options[0][weight]"]', '0')
                ->type('input[name="options[1][label]"]', 'Kadang-kadang')
                ->type('input[name="options[1][weight]"]', '1')
                ->type('input[name="options[2][label]"]', 'Hampir setiap hari')
                ->type('input[name="options[2][weight]"]', '3')
                ->assertSeeIn('#pratinjau-opsi-list', 'Hampir setiap hari')
                ->press('Simpan Pertanyaan')
                ->waitForText('Seberapa sering kamu naik ojek online setiap minggu?')
                ->assertPathIs('/admin/bank-kuis');
        });
    }

    public function test_galeri_lencana_menampilkan_lencana_terkunci(): void
    {
        $this->seed(\Database\Seeders\ManajemenLencanaSeeder015::class);
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->loginAs($pengguna)->visit('/lencana')
                ->assertSee('Terbuka 0 dari')
                ->assertSee('Terkunci');
        });
    }

    public function test_papan_peringkat_tanpa_admin_dan_bilah_posisi_tidak_menutupi_sidebar(): void
    {
        $this->buatAdmin(['name' => 'Admin Tidak Ikut']);
        foreach (['Rina', 'Dodi', 'Sinta'] as $i => $nama) {
            $this->buatPengguna(['name' => $nama.' Peringkat', 'points' => 100 * ($i + 1)]);
        }
        $saya = $this->buatPengguna(['name' => 'Saya Peserta']);

        $this->browse(function (Browser $browser) use ($saya) {
            $browser->loginAs($saya)->visit('/papan-peringkat')
                ->assertSee('Sinta Peringkat')
                ->assertDontSee('Admin Tidak Ikut')
                ->waitFor('.ct-baris-saya-tetap');

            [$tepiKananSidebar, $tepiKiriBilah] = $browser->script(
                "return [document.querySelector('.ap-sisi').getBoundingClientRect().right, document.querySelector('.ct-baris-saya-tetap').getBoundingClientRect().left];"
            )[0];
            $this->assertGreaterThanOrEqual($tepiKananSidebar, $tepiKiriBilah, 'Bilah "Posisi Kamu" menutupi sidebar.');
        });
    }
}
