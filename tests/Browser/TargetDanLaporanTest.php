<?php

namespace Tests\Browser;

use App\Models\SavedReport022;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SiapkanData;
use Tests\DuskTestCase;

// PBI-10 Target Emisi, PBI-11 Pengingat, PBI-12 Laporan (Alvin-022)
class TargetDanLaporanTest extends DuskTestCase
{
    use DatabaseMigrations, SiapkanData;

    public function test_tetapkan_target_lewat_panel_lalu_cincin_kemajuan_tampil(): void
    {
        $pengguna = $this->buatPengguna();

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->loginAs($pengguna)->visit('/target-emisi')
                ->press('Tetapkan Target')
                ->waitFor('#panel-target-baru-batas')
                ->click('#panel-target-baru-batas') // panel tidak boleh tertutup saat isian diklik
                ->type('#panel-target-baru-batas', '150')
                ->within('#panel-target-baru', fn (Browser $panel) => $panel->press('Tetapkan Target'))
                ->waitForText('Target ditetapkan.')
                // Sisa kuota dimuat lewat /api/target/berjalan setelah halaman tampil
                ->waitUntilMissingText('Memuat sisa kuota', 10)
                ->assertSee('150');
        });

        $this->assertSame(1, DB::table('targets')->where('user_id', $pengguna->id)->count());
    }

    public function test_pengingat_bisa_dimatikan_lewat_saklar(): void
    {
        $pengguna = $this->buatPengguna();
        DB::table('reminders')->insert(['user_id' => $pengguna->id, 'remind_time' => '20:00:00', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->browse(function (Browser $browser) use ($pengguna) {
            $browser->loginAs($pengguna)->visit('/pengingat')
                ->assertSee('20.00')
                ->click('label.ct-saklar')
                ->pause(800);
        });

        $this->assertFalse((bool) DB::table('reminders')->where('user_id', $pengguna->id)->value('is_active'));
    }

    public function test_susun_laporan_dengan_pratinjau_lalu_simpan(): void
    {
        $faktor = $this->siapkanMasterEmisi();
        $pengguna = $this->buatPengguna();
        $hariIni = now('Asia/Jakarta');
        $this->catatAktivitas($pengguna, $faktor['listrik'], 30, $hariIni->toDateString());
        $this->catatAktivitas($pengguna, $faktor['motor'], 10, $hariIni->toDateString());

        $this->browse(function (Browser $browser) use ($pengguna, $hariIni) {
            $browser->loginAs($pengguna)->visit('/laporan/susun')
                ->type('#laporan-judul', 'Laporan Dusk')
                ->script("const m=document.querySelector('#laporan-mulai'),s=document.querySelector('#laporan-selesai');"
                    ."m.value='{$hariIni->copy()->startOfMonth()->toDateString()}';s.value='{$hariIni->toDateString()}';"
                    ."m.dispatchEvent(new Event('change',{bubbles:true}));s.dispatchEvent(new Event('change',{bubbles:true}));");

            $browser->waitForTextIn('[data-total]', '40')
                ->press('Simpan Laporan')
                ->waitForText('Laporan Dusk')
                ->assertSee('Rincian per kategori')
                ->assertSee('Listrik');
        });

        $this->assertSame(1, SavedReport022::where('user_id', $pengguna->id)->count());
    }
}
