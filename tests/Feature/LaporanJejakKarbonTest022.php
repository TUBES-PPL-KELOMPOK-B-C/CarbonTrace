<?php

namespace Tests\Feature;

use App\Models\SavedReport022;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LaporanJejakKarbonTest022 extends TestCase
{
    use RefreshDatabase;

    private User $pengguna;

    /** @var array<string, array{kategori: int, faktor: int}> */
    private array $data = [];

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:00', 'Asia/Jakarta'));
        $this->pengguna = User::factory()->create();

        foreach (['Transportasi', 'Listrik', 'Makanan'] as $nama) {
            $kategori = DB::table('categories')->insertGetId(['name' => $nama, 'is_active' => true]);
            $faktor = DB::table('emission_factors')->insertGetId(['category_id' => $kategori, 'name' => $nama, 'unit' => 'kg', 'factor_value' => 1, 'is_active' => true]);
            $this->data[$nama] = ['kategori' => $kategori, 'faktor' => $faktor];
        }
    }

    private function catat(string $kategori, string $tanggal, float $emisi, ?User $pemilik = null): int
    {
        return DB::table('activities')->insertGetId([
            'user_id' => ($pemilik ?? $this->pengguna)->id, 'emission_factor_id' => $this->data[$kategori]['faktor'],
            'amount' => 1, 'emission' => $emisi, 'activity_date' => $tanggal,
        ]);
    }

    private function isian(array $ubah = []): array
    {
        return $ubah + ['title' => 'Laporan November', 'period_start' => '2026-11-01', 'period_end' => '2026-11-25'];
    }

    private function laporan(array $ubah = []): SavedReport022
    {
        return SavedReport022::create($ubah + $this->isian() + ['user_id' => $this->pengguna->id, 'category_scope' => '']);
    }

    public function test_tamu_dipantulkan_dan_keadaan_kosong_tampil(): void
    {
        $this->get(route('laporan.index'))->assertRedirect(route('auth.masuk'));
        $this->actingAs($this->pengguna)->get(route('laporan.index'))
            ->assertSee('Belum ada laporan tersimpan. Susun laporan pertamamu untuk melihat rekap emisi dalam satu halaman.');
    }

    public function test_menolak_ketika_title_kosong_atau_terlalu_panjang(): void
    {
        $this->actingAs($this->pengguna)->post(route('laporan.store'), $this->isian(['title' => '']))
            ->assertSessionHasErrors(['title' => 'Judul laporan wajib diisi.']);
        $this->post(route('laporan.store'), $this->isian(['title' => str_repeat('a', 121)]))
            ->assertSessionHasErrors(['title' => 'Judul laporan maksimal 120 karakter.']);
    }

    public function test_menolak_ketika_periode_kosong_rusak_atau_terbalik(): void
    {
        $this->actingAs($this->pengguna)->post(route('laporan.store'), ['title' => 'A'])
            ->assertSessionHasErrors(['period_start' => 'Tanggal mulai wajib diisi.', 'period_end' => 'Tanggal selesai wajib diisi.']);
        $this->post(route('laporan.store'), $this->isian(['period_start' => 'bukan-tanggal']))->assertSessionHasErrors('period_start');
        $this->post(route('laporan.store'), $this->isian(['period_start' => '2026-11-20', 'period_end' => '2026-11-10']))
            ->assertSessionHasErrors(['period_end' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.']);
    }

    public function test_menolak_period_end_melewati_hari_ini_dan_rentang_lebih_dari_366_hari(): void
    {
        $this->actingAs($this->pengguna)->post(route('laporan.store'), $this->isian(['period_end' => '2026-11-26']))
            ->assertSessionHasErrors(['period_end' => 'Tanggal selesai tidak boleh melewati hari ini.']);

        // 2025-11-25 s.d. 2026-11-25 tepat 366 hari: masih boleh; satu hari lebih panjang ditolak
        $this->post(route('laporan.store'), $this->isian(['period_start' => '2025-11-25']))->assertSessionHasNoErrors();
        $this->post(route('laporan.store'), $this->isian(['period_start' => '2025-11-24']))
            ->assertSessionHasErrors(['period_end' => 'Rentang laporan maksimal satu tahun.']);
    }

    public function test_menolak_kategori_yang_tidak_ada(): void
    {
        $this->actingAs($this->pengguna)->post(route('laporan.store'), $this->isian(['kategori' => [9999]]))
            ->assertSessionHasErrors(['kategori.0' => 'Kategori yang dipilih tidak tersedia.']);
    }

    public function test_membuat_mengubah_dan_menghapus_laporan_tanpa_menghapus_aktivitas(): void
    {
        $this->catat('Listrik', '2026-11-05', 10);
        $listrik = $this->data['Listrik']['kategori'];

        $this->actingAs($this->pengguna)->post(route('laporan.store'), $this->isian(['kategori' => [$listrik]]));
        $laporan = SavedReport022::sole();
        $this->assertSame((string) $listrik, $laporan->category_scope);

        $this->put(route('laporan.update', $laporan), $this->isian(['title' => 'Judul baru', 'kategori' => array_column($this->data, 'kategori')]))
            ->assertSessionHas('sukses', 'Laporan diperbarui.');
        $this->assertSame(['Judul baru', ''], [$laporan->fresh()->title, $laporan->fresh()->category_scope]);

        $this->delete(route('laporan.destroy', $laporan))->assertRedirect(route('laporan.index'));
        $this->assertModelMissing($laporan);
        $this->assertDatabaseCount('activities', 1);
    }

    public function test_pratinjau_menghitung_tanpa_menyimpan(): void
    {
        $this->catat('Transportasi', '2026-11-01', 60);
        $this->catat('Listrik', '2026-11-10', 30);
        $this->catat('Makanan', '2026-11-10', 10);
        $this->catat('Listrik', '2026-10-31', 99);                              // di luar rentang
        $this->catat('Listrik', '2026-11-10', 500, User::factory()->create());  // milik orang lain

        $this->actingAs($this->pengguna)
            ->postJson(route('laporan.pratinjau'), ['period_start' => '2026-11-01', 'period_end' => '2026-11-10'])
            ->assertOk()
            ->assertJsonPath('total', 100)
            ->assertJsonPath('rata_harian', 10)
            ->assertJsonPath('per_kategori.0.nama', 'Transportasi')
            ->assertJsonPath('per_kategori.0.persen', 60)
            ->assertJsonCount(10, 'tren')
            ->assertJsonPath('tren.9', ['tanggal' => '2026-11-10', 'emisi' => 40]);

        $this->postJson(route('laporan.pratinjau'), ['period_start' => '2026-11-01', 'period_end' => '2026-11-10', 'kategori' => [$this->data['Listrik']['kategori']]])
            ->assertJsonPath('total', 30)->assertJsonCount(1, 'per_kategori');

        $this->assertDatabaseCount('saved_reports', 0);
    }

    public function test_jumlah_persentase_tepat_seratus(): void
    {
        foreach (array_keys($this->data) as $nama) {
            $this->catat($nama, '2026-11-10', 1);
        }

        $persen = $this->actingAs($this->pengguna)
            ->postJson(route('laporan.pratinjau'), ['period_start' => '2026-11-01', 'period_end' => '2026-11-25'])
            ->json('per_kategori.*.persen');

        $this->assertEqualsWithDelta(100, array_sum($persen), 0.0001);
    }

    public function test_mengoreksi_satu_aktivitas_membuat_angka_laporan_ikut_berubah(): void
    {
        $id = $this->catat('Listrik', '2026-11-25', 10);
        $laporan = $this->laporan();

        $this->actingAs($this->pengguna)->get(route('laporan.show', $laporan))->assertSee('10,00');
        DB::table('activities')->where('id', $id)->update(['emission' => 25.5]);
        $this->get(route('laporan.show', $laporan))->assertSee('25,50')->assertDontSee('10,00');
    }

    public function test_rentang_tanpa_catatan_dan_kategori_yang_dihapus_tetap_bisa_dibuka(): void
    {
        $this->actingAs($this->pengguna)->get(route('laporan.show', $this->laporan()))
            ->assertOk()->assertSee('Tidak ada catatan pada rentang ini.');

        $hilang = $this->data['Makanan']['kategori'];
        $laporan = $this->laporan(['category_scope' => (string) $hilang]);
        DB::table('emission_factors')->where('category_id', $hilang)->delete();
        DB::table('categories')->where('id', $hilang)->delete();

        $this->get(route('laporan.show', $laporan))->assertOk()->assertSee('Kategori dihapus');
    }

    public function test_rentang_satu_tahun_tetap_cepat(): void
    {
        for ($i = 0; $i < 366; $i++) {
            $this->catat('Listrik', Carbon::parse('2025-11-25')->addDays($i)->toDateString(), 1);
        }
        $laporan = $this->laporan(['period_start' => '2025-11-25']);

        $mulai = microtime(true);
        $this->actingAs($this->pengguna)->get(route('laporan.show', $laporan))->assertOk()->assertSee('366,00');
        $this->assertLessThan(3, microtime(true) - $mulai);
    }

    public function test_laporan_pengguna_lain_tidak_dapat_dibuka_diubah_atau_dihapus(): void
    {
        $laporan = $this->laporan();

        $this->actingAs(User::factory()->create())->get(route('laporan.show', $laporan))->assertForbidden();
        $this->put(route('laporan.update', $laporan), $this->isian())->assertForbidden();
        $this->delete(route('laporan.destroy', $laporan))->assertForbidden();
        $this->get(route('laporan.index'))->assertDontSee('Laporan November');
    }
}
