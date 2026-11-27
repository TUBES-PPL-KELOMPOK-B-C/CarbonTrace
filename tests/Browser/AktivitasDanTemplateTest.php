<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SiapkanData;
use Tests\DuskTestCase;

// PBI-04 Pencatatan Aktivitas dan PBI-05 Template Aktivitas Rutin
class AktivitasDanTemplateTest extends DuskTestCase
{
    use DatabaseMigrations, SiapkanData;

    public function test_catat_aktivitas_dengan_pratinjau_emisi_lalu_muncul_di_riwayat(): void
    {
        $faktor = $this->siapkanMasterEmisi();
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna, $faktor) {
            $browser->loginAs($pengguna)->visit('/aktivitas/buat')
                ->click('[onclick^="pilihKategori('.$faktor['kategoriListrik'].',"]')
                ->select('#pemilih-faktor', (string) $faktor['listrik'])
                ->type('#input-jumlah', '10')
                ->waitForTextIn('#pratinjau-angka', '7,9')
                ->press('Simpan Catatan')
                ->waitForText('Listrik PLN Uji')
                ->assertPathBeginsWith('/aktivitas');
        });

        $this->assertSame(1, DB::table('activities')->where('user_id', $pengguna->id)->count());
    }

    // Regresi: menu titik tiga pada baris terakhir dulu terpotong wadah tabel, sehingga tombol Hapus
    // tidak terlihat.
    public function test_hapus_catatan_lewat_modal_bisa_dibatalkan(): void
    {
        $faktor = $this->siapkanMasterEmisi();
        $pengguna = $this->buatPengguna();
        $this->catatAktivitas($pengguna, $faktor['listrik'], 7.9, now('Asia/Jakarta')->toDateString());

        $id = DB::table('activities')->value('id');

        $this->browse(function (Browser $browser) use ($pengguna, $id) {
            $browser->loginAs($pengguna)->visit('/aktivitas')
                ->assertMissing('#modal-hapus')
                ->click('[aria-label="Menu aksi catatan"]')
                ->waitUntil("!document.getElementById('menu-{$id}').classList.contains('hidden')")
                // Menu tidak terpotong wadah tabel: tombol Hapus benar-benar ada di titik itu di layar
                ->assertScript("(() => { const t = document.querySelector('#menu-{$id} button'), r = t.getBoundingClientRect(); return document.elementFromPoint(r.left + r.width / 2, r.top + r.height / 2)?.closest('button') === t; })()")
                ->click("#menu-{$id} button")
                ->waitFor('#modal-hapus')
                ->within('#modal-hapus', fn (Browser $m) => $m->press('Batal'))
                ->waitUntilMissing('#modal-hapus')
                ->assertSee('Listrik PLN Uji')
                ->click('[aria-label="Menu aksi catatan"]')
                ->waitUntil("!document.getElementById('menu-{$id}').classList.contains('hidden')")
                // Menu tidak terpotong wadah tabel: tombol Hapus benar-benar ada di titik itu di layar
                ->assertScript("(() => { const t = document.querySelector('#menu-{$id} button'), r = t.getBoundingClientRect(); return document.elementFromPoint(r.left + r.width / 2, r.top + r.height / 2)?.closest('button') === t; })()")
                ->click("#menu-{$id} button")
                ->waitFor('#modal-hapus')
                ->press('Hapus Catatan')
                ->waitForText('Belum ada catatan');
        });

        $this->assertSame(0, DB::table('activities')->count());
    }

    // Regresi: panel dulu langsung tertutup saat kolom di dalamnya diklik.
    public function test_panel_template_tetap_terbuka_saat_isian_diklik_lalu_template_tersimpan(): void
    {
        $faktor = $this->siapkanMasterEmisi();
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna, $faktor) {
            $browser->loginAs($pengguna)->visit('/template-aktivitas')
                ->press('Buat Template Baru')
                ->waitFor('#tmpl-nama')
                ->click('#tmpl-nama')
                ->pause(500)
                ->assertVisible('#tmpl-nama')
                ->type('#tmpl-nama', 'Motor ke kampus')
                ->select('#tmpl-faktor', (string) $faktor['motor'])
                ->type('#tmpl-jumlah', '12')
                ->press('Simpan Template')
                ->waitForText('Motor ke kampus');
        });

        $this->assertSame(1, DB::table('activity_templates')->where('user_id', $pengguna->id)->count());
    }
}
