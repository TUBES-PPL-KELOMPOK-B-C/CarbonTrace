<?php

namespace Tests\Feature;

use App\Models\Reminder022;
use App\Models\User;
use App\Notifications\PengingatPencatatan022;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PengingatPencatatanHarianTest022 extends TestCase
{
    use RefreshDatabase;

    private User $pengguna;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-15 19:00', 'Asia/Jakarta'));
        $this->pengguna = User::factory()->create();
    }

    private function pengingat(string $jam, bool $aktif = true, ?User $pemilik = null): Reminder022
    {
        return Reminder022::create(['user_id' => ($pemilik ?? $this->pengguna)->id, 'remind_time' => $jam, 'is_active' => $aktif]);
    }

    private function catatAktivitas(string $tanggal): void
    {
        $kategori = DB::table('categories')->insertGetId(['name' => 'Listrik', 'is_active' => true]);
        $faktor = DB::table('emission_factors')->insertGetId(['category_id' => $kategori, 'name' => 'PLN', 'unit' => 'kWh', 'factor_value' => 0.681, 'is_active' => true]);
        DB::table('activities')->insert([
            'user_id' => $this->pengguna->id, 'emission_factor_id' => $faktor, 'amount' => 1, 'emission' => 0.681,
            'activity_date' => $tanggal, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_menyetel_pengingat_baru(): void
    {
        $this->actingAs($this->pengguna)
            ->post(route('pengingat.store'), ['remind_time' => '20.00'])
            ->assertRedirect(route('pengingat.index'))
            ->assertSessionHas('sukses', 'Pengingat disetel pukul 20.00.');

        $this->assertDatabaseHas('reminders', ['user_id' => $this->pengguna->id, 'remind_time' => '20:00:00', 'is_active' => true]);
    }

    public function test_menolak_ketika_remind_time_kosong_atau_bukan_format_24_jam(): void
    {
        $this->actingAs($this->pengguna)->post(route('pengingat.store'), [])
            ->assertSessionHasErrors(['remind_time' => 'Jam pengingat wajib diisi.']);
        $this->post(route('pengingat.store'), ['remind_time' => '25:00'])
            ->assertSessionHasErrors(['remind_time' => 'Gunakan format jam 24 jam, misalnya 20:00.']);
    }

    public function test_menolak_dua_pengingat_di_jam_yang_sama(): void
    {
        $this->pengingat('20:00');

        $this->actingAs($this->pengguna)->post(route('pengingat.store'), ['remind_time' => '20:00'])
            ->assertSessionHasErrors(['remind_time' => 'Sudah ada pengingat di jam ini.']);

        // Jam yang sama milik pengguna lain tidak masalah
        $this->actingAs(User::factory()->create())->post(route('pengingat.store'), ['remind_time' => '20:00'])
            ->assertSessionHasNoErrors();
    }

    public function test_pengingat_keempat_ditolak_dengan_pesan_jelas(): void
    {
        foreach (['07:00', '12:00', '20:00'] as $jam) {
            $this->pengingat($jam);
        }

        $this->actingAs($this->pengguna)->post(route('pengingat.store'), ['remind_time' => '21:00'])
            ->assertSessionHasErrors(['remind_time' => 'Maksimal tiga pengingat aktif.']);

        $this->get(route('pengingat.index'))->assertDontSee('Tambah pengingat');
    }

    public function test_menyalakan_pengingat_keempat_lewat_saklar_juga_ditolak(): void
    {
        foreach (['07:00', '12:00', '20:00'] as $jam) {
            $this->pengingat($jam);
        }
        $mati = $this->pengingat('21:00', aktif: false);

        $this->actingAs($this->pengguna)->patch(route('pengingat.saklar', $mati), ['is_active' => '1'])
            ->assertSessionHas('galat', 'Maksimal tiga pengingat aktif.');
        $this->assertFalse($mati->fresh()->is_active);

        $this->patch(route('pengingat.saklar', $mati->fresh()), ['is_active' => '0'])->assertSessionHas('sukses');
    }

    public function test_mengubah_jam_dan_hanya_pemilik_yang_boleh(): void
    {
        $pengingat = $this->pengingat('20:00');

        $this->actingAs(User::factory()->create())->put(route('pengingat.update', $pengingat), ['remind_time' => '21:00'])->assertForbidden();
        $this->delete(route('pengingat.destroy', $pengingat))->assertForbidden();

        $this->actingAs($this->pengguna)->put(route('pengingat.update', $pengingat), ['remind_time' => '21:30'])
            ->assertSessionHas('sukses', 'Pengingat disetel pukul 21.30.');
        $this->assertSame('21:30', $pengingat->fresh()->jam());
    }

    public function test_pengingat_terkirim_pada_jam_yang_ditetapkan(): void
    {
        Notification::fake();
        $this->pengingat('20:00');

        $this->travelTo(Carbon::parse('2026-11-15 19:59', 'Asia/Jakarta'));
        $this->artisan('pengingat:kirim')->assertSuccessful();
        Notification::assertNothingSent();

        $this->travelTo(Carbon::parse('2026-11-15 20:00:30', 'Asia/Jakarta'));
        $this->artisan('pengingat:kirim')->expectsOutputToContain('1 pengingat terkirim')->assertSuccessful();
        Notification::assertSentTo($this->pengguna, PengingatPencatatan022::class, fn ($n) => $n->jam === '20.00');

        // Perintah berjalan dua kali di menit yang sama: tidak dikirim ganda
        $this->artisan('pengingat:kirim');
        Notification::assertSentToTimes($this->pengguna, PengingatPencatatan022::class, 1);
    }

    public function test_pengguna_yang_sudah_mencatat_hari_itu_tidak_menerima_pengingat(): void
    {
        Notification::fake();
        $this->pengingat('20:00');
        $this->catatAktivitas('2026-11-15');

        $this->travelTo(Carbon::parse('2026-11-15 20:00', 'Asia/Jakarta'));
        $this->artisan('pengingat:kirim');

        Notification::assertNothingSent();
    }

    public function test_pengingat_nonaktif_dan_akun_nonaktif_tidak_dikirimi(): void
    {
        Notification::fake();
        $this->pengingat('20:00', aktif: false);
        $akunMati = User::factory()->create(['is_active' => false]);
        $this->pengingat('20:00', pemilik: $akunMati);

        $this->travelTo(Carbon::parse('2026-11-15 20:00', 'Asia/Jakarta'));
        $this->artisan('pengingat:kirim');

        Notification::assertNothingSent();
    }

    public function test_halaman_kosong_dan_daftar_pengingat(): void
    {
        $this->actingAs($this->pengguna)->get(route('pengingat.index'))
            ->assertOk()
            ->assertSee('Belum ada pengingat. Pencatatan yang bolong membuat tren emisimu sulit dibaca.');

        $this->pengingat('07:30');
        $this->get(route('pengingat.index'))->assertSee('07.30')->assertSee('Tambah pengingat');
    }
}
