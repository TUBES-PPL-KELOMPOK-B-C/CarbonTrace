<?php

namespace Tests\Feature;

use App\Models\Badge015;
use App\Models\User314;
use App\Services\PemeriksaSyaratLencana015;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// PBI-13 Manajemen Lencana (KF-14) - Modul Gamifikasi (Man-015).
class ManajemenLencanaTest015 extends TestCase
{
    use RefreshDatabase;

    private User314 $admin;

    private User314 $pengguna;

    protected function setUp(): void
    {
        parent::setUp();
        // 23 November 2026, pukul 09.00 WIB
        Carbon::setTestNow(Carbon::parse('2026-11-23 09:00', 'Asia/Jakarta'));

        $peranAdmin = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $peranPengguna = Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);

        $this->admin = User314::factory()->create(['role_id' => $peranAdmin->id]);
        $this->admin->assignRole('administrator');

        $this->pengguna = User314::factory()->create(['role_id' => $peranPengguna->id]);
        $this->pengguna->assignRole('pengguna');
    }

    private function isian(array $ubah = []): array
    {
        return $ubah + [
            'name' => 'Pengumpul Poin',
            'description' => 'Kumpulkan 500 poin.',
            'icon' => 'piala',
            'type' => 'poin',
            'required_value' => '500',
            'is_active' => '1',
        ];
    }

    private function lencana(array $ubah = []): Badge015
    {
        return Badge015::create($ubah + [
            'name' => 'Langkah Pertama',
            'description' => 'Kumpulkan 50 poin pertamamu.',
            'icon' => 'bintang',
            'type' => 'poin',
            'required_value' => 50,
            'is_active' => true,
        ]);
    }

    // --- Akses -------------------------------------------------------------------------------

    public function test_tamu_dipantulkan_ke_halaman_masuk(): void
    {
        $this->get(route('lencana.index'))->assertRedirect(route('auth.masuk'));
        $this->get(route('lencana.galeri'))->assertRedirect(route('auth.masuk'));
    }

    public function test_pengguna_biasa_tidak_boleh_membuka_pengelolaan_lencana(): void
    {
        $this->actingAs($this->pengguna)->get(route('lencana.index'))->assertForbidden();
    }

    public function test_administrator_dapat_membuka_pengelolaan_lencana(): void
    {
        $this->lencana();

        $this->actingAs($this->admin)->get(route('lencana.index'))
            ->assertOk()
            ->assertSee('Langkah Pertama');
    }

    // --- Menolak ketika aturan dilanggar -----------------------------------------------------

    public function test_menolak_ketika_name_kosong(): void
    {
        $this->actingAs($this->admin)
            ->post(route('lencana.store'), $this->isian(['name' => '']))
            ->assertSessionHasErrors(['name' => 'Nama lencana wajib diisi.']);

        $this->assertDatabaseCount('badges', 0);
    }

    public function test_menolak_ketika_type_kosong(): void
    {
        $this->actingAs($this->admin)
            ->post(route('lencana.store'), $this->isian(['type' => '']))
            ->assertSessionHasErrors(['type' => 'Jenis syarat wajib dipilih.']);

        $this->assertDatabaseCount('badges', 0);
    }

    public function test_menolak_ketika_required_value_kosong(): void
    {
        $this->actingAs($this->admin)
            ->post(route('lencana.store'), $this->isian(['required_value' => '']))
            ->assertSessionHasErrors(['required_value' => 'Angka syarat wajib diisi.']);

        $this->assertDatabaseCount('badges', 0);
    }

    public function test_menolak_ketika_icon_kosong(): void
    {
        $this->actingAs($this->admin)
            ->post(route('lencana.store'), $this->isian(['icon' => '']))
            ->assertSessionHasErrors(['icon' => 'Ikon lencana wajib dipilih.']);

        $this->assertDatabaseCount('badges', 0);
    }

    public function test_menolak_jenis_syarat_yang_tidak_dikenali(): void
    {
        $this->actingAs($this->admin)
            ->post(route('lencana.store'), $this->isian(['type' => 'poinn']))
            ->assertSessionHasErrors('type');

        $this->assertDatabaseCount('badges', 0);
    }

    // --- CRUD --------------------------------------------------------------------------------

    public function test_menambah_lencana_baru(): void
    {
        $this->actingAs($this->admin)
            ->post(route('lencana.store'), $this->isian(['required_value' => '500,5']))
            ->assertRedirect(route('lencana.index'))
            ->assertSessionHas('sukses', 'Lencana ditambahkan.');

        $this->assertDatabaseHas('badges', [
            'name' => 'Pengumpul Poin',
            'type' => 'poin',
            'required_value' => 500.5,
            'is_active' => true,
        ]);
    }

    public function test_mengubah_syarat_lencana(): void
    {
        $lencana = $this->lencana();

        $this->actingAs($this->admin)
            ->put(route('lencana.update', $lencana), $this->isian([
                'name' => 'Langkah Pertama',
                'required_value' => '75',
            ]))
            ->assertRedirect(route('lencana.index'))
            ->assertSessionHas('sukses', 'Syarat lencana diperbarui.');

        $this->assertDatabaseHas('badges', ['id' => $lencana->id, 'required_value' => 75]);
    }

    public function test_menonaktifkan_dan_mengaktifkan_lencana(): void
    {
        $lencana = $this->lencana();

        $this->actingAs($this->admin)->patch(route('lencana.status', $lencana))
            ->assertSessionHas('sukses', 'Lencana dinonaktifkan.');
        $this->assertDatabaseHas('badges', ['id' => $lencana->id, 'is_active' => false]);

        $this->actingAs($this->admin)->patch(route('lencana.status', $lencana))
            ->assertSessionHas('sukses', 'Lencana diaktifkan.');
        $this->assertDatabaseHas('badges', ['id' => $lencana->id, 'is_active' => true]);
    }

    public function test_lencana_yang_belum_diraih_dapat_dihapus(): void
    {
        $lencana = $this->lencana();

        $this->actingAs($this->admin)->delete(route('lencana.destroy', $lencana))
            ->assertSessionHas('sukses', 'Lencana dihapus.');

        $this->assertDatabaseMissing('badges', ['id' => $lencana->id]);
    }

    // --- Kriteria selesai --------------------------------------------------------------------

    public function test_memastikan_lencana_yang_pernah_diraih_tidak_dapat_dihapus_permanen(): void
    {
        $lencana = $this->lencana();
        $this->pengguna->update(['points' => 50]);
        app(PemeriksaSyaratLencana015::class)->periksa($this->pengguna);

        $this->assertDatabaseCount('badge_user', 1);

        $this->actingAs($this->admin)->delete(route('lencana.destroy', $lencana))
            ->assertSessionHas('galat', 'Lencana ini sudah diraih 1 pengguna. Nonaktifkan saja.');

        $this->assertDatabaseHas('badges', ['id' => $lencana->id]);
    }

    public function test_memastikan_lencana_otomatis_diberikan_ketika_syaratnya_terpenuhi(): void
    {
        $lencana = $this->lencana(['required_value' => 50]);
        $this->pengguna->update(['points' => 50]);

        $this->actingAs($this->pengguna)->get(route('lencana.galeri'))->assertOk();

        $this->assertDatabaseHas('badge_user', [
            'user_id' => $this->pengguna->id,
            'badge_id' => $lencana->id,
        ]);
    }

    public function test_memastikan_tidak_muncul_baris_ganda_pada_badge_user(): void
    {
        $this->lencana(['required_value' => 50]);
        $this->pengguna->update(['points' => 50]);

        $pemeriksa = app(PemeriksaSyaratLencana015::class);

        // Pemeriksaan dijalankan tiga kali, seperti pengguna membuka galerinya berulang kali.
        $pemeriksa->periksa($this->pengguna);
        $pemeriksa->periksa($this->pengguna->fresh());
        $pemeriksa->periksa($this->pengguna->fresh());

        $this->assertDatabaseCount('badge_user', 1);
    }

    public function test_kunci_unik_menolak_baris_ganda_meski_ditulis_langsung(): void
    {
        $lencana = $this->lencana();
        $baris = [
            'user_id' => $this->pengguna->id,
            'badge_id' => $lencana->id,
            'earned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('badge_user')->insert($baris);
        // insertOrIgnore, bukan insert: baris kedua ditolak kunci unik tanpa melemparkan galat.
        DB::table('badge_user')->insertOrIgnore($baris);

        $this->assertDatabaseCount('badge_user', 1);
    }

    // --- Kasus batas -------------------------------------------------------------------------

    public function test_syarat_diturunkan_membuat_lencana_langsung_diraih(): void
    {
        $lencana = $this->lencana(['required_value' => 1000]);
        $this->pengguna->update(['points' => 100]);

        $pemeriksa = app(PemeriksaSyaratLencana015::class);
        $pemeriksa->periksa($this->pengguna);
        $this->assertDatabaseCount('badge_user', 0);

        $lencana->update(['required_value' => 50]);
        $pemeriksa->periksa($this->pengguna->fresh());

        $this->assertDatabaseHas('badge_user', [
            'user_id' => $this->pengguna->id,
            'badge_id' => $lencana->id,
        ]);
    }

    public function test_syarat_dinaikkan_tidak_mencabut_lencana_yang_sudah_diraih(): void
    {
        $lencana = $this->lencana(['required_value' => 50]);
        $this->pengguna->update(['points' => 50]);

        $pemeriksa = app(PemeriksaSyaratLencana015::class);
        $pemeriksa->periksa($this->pengguna);
        $this->assertDatabaseCount('badge_user', 1);

        $lencana->update(['required_value' => 5000]);
        $pemeriksa->periksa($this->pengguna->fresh());

        $this->assertDatabaseCount('badge_user', 1);
    }

    public function test_lencana_nonaktif_tidak_diberikan_tetapi_tetap_terlihat_oleh_peraihnya(): void
    {
        $lencana = $this->lencana(['required_value' => 50]);
        $this->pengguna->update(['points' => 50]);

        $pemeriksa = app(PemeriksaSyaratLencana015::class);
        $pemeriksa->periksa($this->pengguna);

        // Pengguna lain yang syaratnya juga terpenuhi tidak lagi mendapat lencana nonaktif.
        $lencana->update(['is_active' => false]);
        $orangLain = User314::factory()->create(['points' => 500]);
        $pemeriksa->periksa($orangLain);

        $this->assertDatabaseCount('badge_user', 1);

        // Tetapi yang sudah meraihnya masih melihatnya di galerinya.
        $this->actingAs($this->pengguna)->get(route('lencana.galeri'))
            ->assertOk()
            ->assertSee('Langkah Pertama');

        // Sementara bagi yang belum, lencana itu hilang dari galeri.
        $this->actingAs($orangLain)->get(route('lencana.galeri'))
            ->assertOk()
            ->assertDontSee('Langkah Pertama');
    }

    public function test_galeri_pengguna_baru_menampilkan_seluruh_lencana_dalam_keadaan_terkunci(): void
    {
        $this->lencana(['name' => 'Langkah Pertama', 'required_value' => 50]);
        $this->lencana(['name' => 'Tujuh Hari Lurus', 'type' => 'runtutan', 'required_value' => 7, 'icon' => 'api']);

        $this->actingAs($this->pengguna)->get(route('lencana.galeri'))
            ->assertOk()
            ->assertSee('Langkah Pertama')
            ->assertSee('Tujuh Hari Lurus')
            ->assertSee('Terkunci — 50 poin')
            ->assertSee('0 dari 50 poin');

        $this->assertDatabaseCount('badge_user', 0);
    }

    public function test_kemajuan_dihitung_dari_aktivitas_dan_emisi_yang_tercatat(): void
    {
        $this->lencana(['name' => 'Pencatat Rajin', 'type' => 'aktivitas', 'required_value' => 4, 'icon' => 'buku']);

        $kategori = DB::table('categories')->insertGetId(['name' => 'Listrik', 'is_active' => true]);
        $faktor = DB::table('emission_factors')->insertGetId([
            'category_id' => $kategori, 'name' => 'Listrik PLN', 'unit' => 'kWh',
            'factor_value' => 0.681, 'is_active' => true,
        ]);

        foreach ([1, 2] as $hari) {
            DB::table('activities')->insert([
                'user_id' => $this->pengguna->id, 'emission_factor_id' => $faktor,
                'amount' => 1, 'emission' => 12.5, 'activity_date' => "2026-11-0{$hari}",
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $capaian = app(PemeriksaSyaratLencana015::class)->capaian($this->pengguna);

        $this->assertSame(2.0, $capaian['aktivitas']);
        $this->assertSame(25.0, $capaian['emisi']);

        // Dua dari empat aktivitas: setengah jalan, jadi batang kemajuannya 50 persen.
        $this->actingAs($this->pengguna)->get(route('lencana.galeri'))
            ->assertOk()
            ->assertSee('2 dari 4 aktivitas tercatat');
    }

    public function test_nama_lencana_tidak_boleh_kembar(): void
    {
        $this->lencana();

        $this->actingAs($this->admin)
            ->post(route('lencana.store'), $this->isian(['name' => 'Langkah Pertama']))
            ->assertSessionHasErrors(['name' => 'Sudah ada lencana dengan nama ini.']);

        $this->assertDatabaseCount('badges', 1);
    }
}
