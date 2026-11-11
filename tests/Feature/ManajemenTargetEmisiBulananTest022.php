<?php

namespace Tests\Feature;

use App\Events\PemakaianTargetBerubah022;
use App\Models\Activitie221;
use App\Models\Target022;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ManajemenTargetEmisiBulananTest022 extends TestCase
{
    use RefreshDatabase;

    private User $pengguna;

    private int $faktorId;

    protected function setUp(): void
    {
        parent::setUp();
        // 15 November 2026, pukul 10.00 WIB
        Carbon::setTestNow(Carbon::parse('2026-11-15 10:00', 'Asia/Jakarta'));

        $this->pengguna = User::factory()->create();
        $kategori = DB::table('categories')->insertGetId(['name' => 'Listrik', 'is_active' => true]);
        $this->faktorId = DB::table('emission_factors')->insertGetId([
            'category_id' => $kategori, 'name' => 'Listrik PLN', 'unit' => 'kWh', 'factor_value' => 0.681, 'is_active' => true,
        ]);
    }

    private function target(array $ubah = []): Target022
    {
        return Target022::create($ubah + [
            'user_id' => $this->pengguna->id,
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
            'emission_limit' => 100,
        ]);
    }

    private function catat(string $tanggal, float $emisi): Activitie221
    {
        return Activitie221::create([
            'user_id' => $this->pengguna->id, 'emission_factor_id' => $this->faktorId,
            'amount' => 1, 'emission' => $emisi, 'activity_date' => $tanggal, 'note' => null,
        ]);
    }

    public function test_tamu_dipantulkan_ke_halaman_masuk(): void
    {
        $this->get(route('target.index'))->assertRedirect(route('auth.masuk'));
    }

    public function test_menetapkan_target_baru(): void
    {
        $this->actingAs($this->pengguna)
            ->post(route('target.store'), ['period_start' => '2026-11-15', 'period_end' => '2026-11-30', 'emission_limit' => '150,5'])
            ->assertRedirect(route('target.index'))
            ->assertSessionHas('sukses', 'Target ditetapkan.');

        $this->assertDatabaseHas('targets', ['user_id' => $this->pengguna->id, 'period_start' => '2026-11-15', 'emission_limit' => 150.5]);
    }

    public function test_menolak_ketika_period_start_dan_period_end_kosong(): void
    {
        $this->actingAs($this->pengguna)
            ->post(route('target.store'), ['emission_limit' => 100])
            ->assertSessionHasErrors(['period_start' => 'Tanggal mulai wajib diisi.', 'period_end' => 'Tanggal selesai wajib diisi.']);
    }

    public function test_menolak_periode_terbalik_dan_periode_yang_sudah_lewat(): void
    {
        $this->actingAs($this->pengguna)
            ->post(route('target.store'), ['period_start' => '2026-12-31', 'period_end' => '2026-12-01', 'emission_limit' => 100])
            ->assertSessionHasErrors(['period_end' => 'Tanggal selesai harus setelah tanggal mulai.']);

        $this->post(route('target.store'), ['period_start' => '2026-10-01', 'period_end' => '2026-10-31', 'emission_limit' => 100])
            ->assertSessionHasErrors(['period_end' => 'Periode ini sudah berakhir. Pilih periode yang masih berjalan atau yang akan datang.']);
    }

    public function test_menolak_ketika_emission_limit_kosong_nol_atau_terlalu_besar(): void
    {
        $data = ['period_start' => '2026-11-15', 'period_end' => '2026-11-30'];

        $this->actingAs($this->pengguna)->post(route('target.store'), $data)
            ->assertSessionHasErrors(['emission_limit' => 'Batas emisi wajib diisi.']);
        $this->post(route('target.store'), $data + ['emission_limit' => 0])
            ->assertSessionHasErrors(['emission_limit' => 'Batas emisi harus lebih besar dari 0.']);
        $this->post(route('target.store'), $data + ['emission_limit' => 10001])
            ->assertSessionHasErrors(['emission_limit' => 'Batas emisi maksimal 10.000 kg CO2e.']);
    }

    public function test_dua_target_dengan_periode_bertabrakan_ditolak(): void
    {
        $this->target();
        $pesan = 'Kamu sudah punya target untuk periode ini. Ubah target yang ada atau pilih periode lain.';

        $this->actingAs($this->pengguna)
            ->post(route('target.store'), ['period_start' => '2026-11-20', 'period_end' => '2026-12-10', 'emission_limit' => 100])
            ->assertSessionHasErrors(['period_start' => $pesan]);

        // Pengguna lain boleh memakai periode yang sama
        $this->actingAs(User::factory()->create())
            ->post(route('target.store'), ['period_start' => '2026-11-20', 'period_end' => '2026-12-10', 'emission_limit' => 100])
            ->assertSessionHasNoErrors();
    }

    public function test_mengubah_periode_hingga_bertabrakan_juga_ditolak(): void
    {
        $this->target();
        $bulanDepan = $this->target(['period_start' => '2026-12-01', 'period_end' => '2026-12-31']);

        $this->actingAs($this->pengguna)
            ->put(route('target.update', $bulanDepan), ['period_start' => '2026-11-25', 'period_end' => '2026-12-31', 'emission_limit' => 100])
            ->assertSessionHasErrors('period_start');

        // Mengubah besaran target sendiri tanpa menggeser periode tetap boleh
        $this->put(route('target.update', $bulanDepan), ['period_start' => '2026-12-01', 'period_end' => '2026-12-31', 'emission_limit' => 90])
            ->assertSessionHas('sukses', 'Target diperbarui.');
        $this->assertSame(90.0, (float) $bulanDepan->fresh()->emission_limit);
    }

    public function test_sisa_kuota_berubah_tepat_setelah_satu_aktivitas_dicatat(): void
    {
        $this->target();
        $this->actingAs($this->pengguna);

        $this->getJson(route('target.berjalan'))->assertJson(['terpakai' => 0, 'sisa' => 100, 'persen' => 0, 'status' => 'aman']);

        $this->catat('2026-11-15', 72.5);

        $this->getJson(route('target.berjalan'))->assertJson([
            'ada_target' => true,
            'periode' => ['mulai' => '2026-11-01', 'selesai' => '2026-11-30'],
            'batas' => 100,
            'terpakai' => 72.5,
            'sisa' => 27.5,
            'persen' => 72.5,
            'status' => 'hampir',
            'sisa_hari' => 16,
        ]);
    }

    public function test_target_tengah_bulan_hanya_menghitung_dari_tanggal_mulai(): void
    {
        $this->target(['period_start' => '2026-11-10']);
        $this->catat('2026-11-05', 40);
        $this->catat('2026-11-12', 10);

        $this->actingAs($this->pengguna)->getJson(route('target.berjalan'))->assertJsonPath('terpakai', 10);
    }

    public function test_target_kedaluwarsa_pindah_ke_riwayat_secara_otomatis(): void
    {
        $this->target();
        $this->catat('2026-11-10', 120);

        $this->actingAs($this->pengguna)->get(route('target.index'))
            ->assertSee('Target berjalan')
            ->assertSee('Target yang periodenya sudah berakhir akan tampil di sini.');

        $this->travelTo(Carbon::parse('2026-12-02 09:00', 'Asia/Jakarta'));

        $this->get(route('target.index'))
            ->assertDontSee('Target berjalan')
            ->assertSee('Belum ada target.')
            ->assertSeeInOrder(['Riwayat target', '1 Nov – 30 Nov 2026', '120 dari 100 kg CO2e', 'Terlampaui']);
    }

    public function test_tanpa_target_api_memberi_tahu_dan_halaman_menyarankan_angka(): void
    {
        $this->catat('2026-11-01', 50);
        $this->catat('2026-11-14', 50);

        $this->actingAs($this->pengguna)->getJson(route('target.berjalan'))->assertExactJson(['ada_target' => false]);
        $this->get(route('target.index'))->assertSee('Saran: 90 kg CO2e');
    }

    public function test_target_yang_sudah_berakhir_tidak_bisa_diubah(): void
    {
        $lama = $this->target(['period_start' => '2026-10-01', 'period_end' => '2026-10-31']);

        $this->actingAs($this->pengguna)
            ->put(route('target.update', $lama), ['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'emission_limit' => 50])
            ->assertSessionHas('galat', 'Target yang periodenya sudah berakhir tidak bisa diubah.');
    }

    public function test_hanya_pemilik_yang_bisa_mengubah_dan_membatalkan(): void
    {
        $target = $this->target();
        $lain = User::factory()->create();

        $this->actingAs($lain)->put(route('target.update', $target), ['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'emission_limit' => 1])->assertForbidden();
        $this->delete(route('target.destroy', $target))->assertForbidden();

        $this->actingAs($this->pengguna)->delete(route('target.destroy', $target))->assertSessionHas('sukses', 'Target dibatalkan.');
        $this->assertModelMissing($target);
    }

    public function test_event_terkirim_saat_aktivitas_dicatat_dan_dikoreksi(): void
    {
        $target = $this->target();
        Event::fake([PemakaianTargetBerubah022::class]);

        $aktivitas = $this->catat('2026-11-15', 120);
        Event::assertDispatched(PemakaianTargetBerubah022::class, fn ($e) => $e->target->is($target) && $e->ringkasan['status'] === 'terlampaui');

        // Koreksi hingga kembali di bawah batas: event membawa status baru, supaya rekomendasi bisa ditarik
        $aktivitas->update(['emission' => 20]);
        Event::assertDispatched(PemakaianTargetBerubah022::class, fn ($e) => $e->ringkasan['status'] === 'aman');
    }
}
