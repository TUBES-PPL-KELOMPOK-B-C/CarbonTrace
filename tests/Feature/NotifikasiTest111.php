<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PengingatOtomatis111;
use App\Notifications\PengingatPencatatan022;
use App\Notifications\PengisianTersimpan111;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// Lonceng notifikasi di bilah atas (fitur pendukung, Alvin-111)
class NotifikasiTest111 extends TestCase
{
    use RefreshDatabase;

    private User $pengguna;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pengguna = User::factory()->create();
    }

    public function test_simpan_yang_berhasil_masuk_ke_lonceng_dengan_pesan_suksesnya(): void
    {
        $this->actingAs($this->pengguna)->post(route('pengingat.store'), ['remind_time' => '20.00'])
            ->assertRedirect(route('pengingat.index'));

        $catatan = $this->pengguna->notifications()->sole();
        $this->assertSame(PengisianTersimpan111::class, $catatan->type);
        $this->assertSame(['jenis' => 'pengisian', 'judul' => 'Pengingat', 'pesan' => 'Pengingat disetel pukul 20.00.', 'tautan' => '/pengingat'], $catatan->data);

        $this->get(route('dashboard.index'))
            ->assertSee('Notifikasi, 1 belum dibaca')
            ->assertSee('Pengingat disetel pukul 20.00.');
    }

    public function test_simpan_yang_gagal_validasi_dan_halaman_biasa_tidak_dicatat(): void
    {
        $this->actingAs($this->pengguna)->post(route('pengingat.store'), ['remind_time' => '25:00'])->assertSessionHasErrors();
        $this->get(route('pengingat.index'))->assertOk();
        $this->post(route('auth.keluar'));

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_membuka_notifikasi_menandai_dibaca_lalu_menuju_halamannya(): void
    {
        $this->pengguna->notify(new PengisianTersimpan111('Target Emisi', 'Target disimpan.', '/target-emisi'));
        $catatan = $this->pengguna->notifications()->sole();

        $this->actingAs($this->pengguna)->get(route('notifikasi.buka', $catatan->id))->assertRedirect('/target-emisi');
        $this->assertNotNull($catatan->fresh()->read_at);
        $this->get(route('dashboard.index'))->assertSee('aria-label="Notifikasi"', false);
    }

    public function test_tautan_ke_situs_lain_tidak_diikuti(): void
    {
        $this->pengguna->notify(new PengisianTersimpan111('X', 'Y', '//situs-lain.test/jebakan'));

        $this->actingAs($this->pengguna)->get(route('notifikasi.buka', $this->pengguna->notifications()->sole()->id))
            ->assertRedirect(route('dashboard.index'));
    }

    public function test_tandai_semua_dibaca_dan_notifikasi_orang_lain_tidak_bisa_dibuka(): void
    {
        $this->pengguna->notify(new PengisianTersimpan111('A', 'Satu.'));
        $this->pengguna->notify(new PengisianTersimpan111('B', 'Dua.'));

        $lain = User::factory()->create();
        $this->actingAs($lain)->get(route('notifikasi.buka', $this->pengguna->notifications()->first()->id))->assertNotFound();

        $this->actingAs($this->pengguna)->post(route('notifikasi.baca-semua'));
        $this->assertSame(0, $this->pengguna->unreadNotifications()->count());
    }

    public function test_pengingat_pbi11_juga_masuk_ke_lonceng(): void
    {
        $this->assertSame(['mail', 'database'], (new PengingatPencatatan022('20.00'))->via($this->pengguna));

        $this->pengguna->notify(new PengingatPencatatan022('20.00'));
        $this->assertSame('Pengingat pukul 20.00', $this->pengguna->notifications()->sole()->data['judul']);
    }

    public function test_pengingat_otomatis_setelah_24_jam_tanpa_catatan_dan_tidak_berulang_dalam_24_jam(): void
    {
        Carbon::setTestNow('2026-11-27 12:00:00');
        $lama = User::factory()->create(['created_at' => now()->subDays(3)]);
        $rajin = User::factory()->create(['created_at' => now()->subDays(3)]);
        $baru = User::factory()->create(['created_at' => now()->subHours(5)]);
        $nonaktif = User::factory()->create(['created_at' => now()->subDays(3), 'is_active' => false]);
        $admin = User::factory()->create(['created_at' => now()->subDays(3)]);
        $admin->assignRole(Role::findOrCreate('administrator', 'web'));
        $this->catatAktivitas($rajin, now()->subHours(3));
        $this->catatAktivitas($lama, now()->subHours(30));

        $this->artisan('notifikasi:pengingat-otomatis')->assertSuccessful();

        foreach ([$lama] as $u) {
            $this->assertSame(1, $u->notifications()->where('type', PengingatOtomatis111::class)->count());
        }
        foreach ([$rajin, $baru, $nonaktif, $admin] as $u) {
            $this->assertSame(0, $u->notifications()->count());
        }

        // Satu jam kemudian belum diulang; 24 jam kemudian diingatkan lagi.
        Carbon::setTestNow(now()->addHour());
        $this->artisan('notifikasi:pengingat-otomatis');
        $this->assertSame(1, $lama->notifications()->count());

        Carbon::setTestNow(now()->addDay());
        $this->artisan('notifikasi:pengingat-otomatis');
        $this->assertSame(2, $lama->notifications()->count());
    }

    private function catatAktivitas(User $pemilik, Carbon $waktu): void
    {
        $kategori = DB::table('categories')->insertGetId(['name' => 'Uji '.$pemilik->id, 'is_active' => true]);
        $faktor = DB::table('emission_factors')->insertGetId(['category_id' => $kategori, 'name' => 'Uji', 'unit' => 'km', 'factor_value' => 1, 'is_active' => true]);
        DB::table('activities')->insert([
            'user_id' => $pemilik->id, 'emission_factor_id' => $faktor, 'amount' => 1, 'emission' => 1,
            'activity_date' => $waktu->toDateString(), 'created_at' => $waktu, 'updated_at' => $waktu,
        ]);
    }
}
