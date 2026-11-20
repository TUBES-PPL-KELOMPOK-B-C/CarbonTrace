<?php

namespace Tests\Feature;

use App\Models\User314;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PapanPeringkatKomunitasTest314 extends TestCase
{
    use RefreshDatabase;

    protected User314 $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Bersihkan cache sebelum setiap pengujian
        Cache::flush();

        // Buat role dasar
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'pengguna',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->user = User314::factory()->create([
            'role_id' => $roleId,
            'is_active' => true,
            'points' => 100,
            'streak_count' => 5,
        ]);
    }

    /**
     * Tamu yang belum login diarahkan ke halaman login.
     */
    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $response = $this->get('/papan-peringkat');
        $response->assertRedirect('/masuk');
    }

    /**
     * Pengguna aktif dapat membuka halaman papan peringkat dengan status 200.
     */
    public function test_pengguna_aktif_dapat_melihat_papan_peringkat(): void
    {
        $response = $this->actingAs($this->user)->get('/papan-peringkat');

        $response->assertStatus(200);
        $response->assertSee('Papan Peringkat Komunitas');
        $response->assertSee('Mingguan');
        $response->assertSee('Bulanan');
        $response->assertSee('Sepanjang Waktu');
    }

    /**
     * Menolak ketika aturan dilanggar: Parameter periode hanya menerima: minggu, bulan, semua.
     */
    public function test_menolak_parameter_periode_tidak_valid(): void
    {
        $response = $this->actingAs($this->user)->get('/papan-peringkat?periode=tahunan');

        $response->assertSessionHasErrors(['periode']);
    }

    /**
     * Menolak ketika aturan dilanggar: Parameter urutan hanya menerima: poin, streak, penurunan.
     */
    public function test_menolak_parameter_urutan_tidak_valid(): void
    {
        $response = $this->actingAs($this->user)->get('/papan-peringkat?urut=nama');

        $response->assertSessionHasErrors(['urut']);
    }

    /**
     * Menolak ketika aturan dilanggar: Nomor halaman bilangan bulat positif.
     */
    public function test_menolak_nomor_halaman_tidak_positif(): void
    {
        $responseNegatif = $this->actingAs($this->user)->get('/papan-peringkat?halaman=0');
        $responseNegatif->assertSessionHasErrors(['halaman']);

        $responseHuruf = $this->actingAs($this->user)->get('/papan-peringkat?halaman=abc');
        $responseHuruf->assertSessionHasErrors(['halaman']);
    }

    /**
     * Memastikan: tiga pilihan periode menghasilkan urutan yang berbeda dan benar.
     */
    public function test_tiga_pilihan_periode_menghasilkan_urutan_berbeda_dan_benar(): void
    {
        Cache::flush();

        // User A: unggul di mingguan (dibuat 1 hari lalu)
        $userA = User314::factory()->create([
            'role_id' => $this->user->role_id,
            'name' => 'User Juara Mingguan',
            'is_active' => true,
            'points' => 500,
        ]);
        DB::table('point_logs')->insert([
            'user_id' => $userA->id,
            'points' => 500,
            'reason' => 'Log Mingguan',
            'created_at' => now()->subDay(),
        ]);

        // User B: unggul di bulanan (dibuat 15 hari lalu)
        $userB = User314::factory()->create([
            'role_id' => $this->user->role_id,
            'name' => 'User Juara Bulanan',
            'is_active' => true,
            'points' => 1200,
        ]);
        DB::table('point_logs')->insert([
            'user_id' => $userB->id,
            'points' => 1200,
            'reason' => 'Log Bulanan',
            'created_at' => now()->subDays(15),
        ]);

        // User C: unggul di sepanjang waktu (dibuat 50 hari lalu)
        $userC = User314::factory()->create([
            'role_id' => $this->user->role_id,
            'name' => 'User Juara Sepanjang Waktu',
            'is_active' => true,
            'points' => 3000,
        ]);
        DB::table('point_logs')->insert([
            'user_id' => $userC->id,
            'points' => 3000,
            'reason' => 'Log Lampau',
            'created_at' => now()->subDays(50),
        ]);

        // Uji Periode Mingguan: Juara 1 adalah User A
        Cache::flush();
        $resMinggu = $this->actingAs($this->user)->getJson('/api/papan-peringkat?periode=minggu&urut=poin');
        $resMinggu->assertStatus(200);
        $dataMinggu = $resMinggu->json('data');
        $this->assertEquals($userA->id, $dataMinggu[0]['user_id']);

        // Uji Periode Bulanan: Juara 1 adalah User B
        Cache::flush();
        $resBulan = $this->actingAs($this->user)->getJson('/api/papan-peringkat?periode=bulan&urut=poin');
        $resBulan->assertStatus(200);
        $dataBulan = $resBulan->json('data');
        $this->assertEquals($userB->id, $dataBulan[0]['user_id']);

        // Uji Periode Semua: Juara 1 adalah User C
        Cache::flush();
        $resSemua = $this->actingAs($this->user)->getJson('/api/papan-peringkat?periode=semua&urut=poin');
        $resSemua->assertStatus(200);
        $dataSemua = $resSemua->json('data');
        $this->assertEquals($userC->id, $dataSemua[0]['user_id']);
    }

    /**
     * Memastikan: pengguna yang belum punya poin tidak membuat halaman galat.
     */
    public function test_pengguna_belum_punya_poin_tidak_membuat_halaman_galat(): void
    {
        Cache::flush();

        $userNol = User314::factory()->create([
            'role_id' => $this->user->role_id,
            'name' => 'Pengguna Baru',
            'points' => 0,
            'streak_count' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($userNol)->get('/papan-peringkat');
        $response->assertStatus(200);
        $response->assertSee('Pengguna Baru');

        // Di API, posisi saya tetap ada dan tidak null
        $apiRes = $this->actingAs($userNol)->getJson('/api/papan-peringkat');
        $apiRes->assertStatus(200);
        $this->assertEquals(0, $apiRes->json('saya.poin'));
        $this->assertGreaterThanOrEqual(1, $apiRes->json('saya.peringkat'));
    }

    /**
     * Memastikan: pengguna nonaktif tidak ikut ditampilkan di papan peringkat.
     */
    public function test_pengguna_nonaktif_dikeluarkan_dari_papan_peringkat(): void
    {
        Cache::flush();

        $userNonaktif = User314::factory()->create([
            'role_id' => $this->user->role_id,
            'name' => 'Pengguna Di-Banned',
            'points' => 9999,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/papan-peringkat?periode=semua&urut=poin');
        $response->assertStatus(200);

        $daftarId = collect($response->json('data'))->pluck('user_id')->toArray();
        $this->assertNotContains($userNonaktif->id, $daftarId);
    }

    /**
     * Memastikan: halaman tetap terbuka di bawah dua detik dengan 500 baris data uji.
     */
    public function test_halaman_terbuka_di_bawah_dua_detik_dengan_500_data(): void
    {
        Cache::flush();

        // Masukkan 500 baris point_logs dengan insert massal
        $records = [];
        for ($i = 0; $i < 500; $i++) {
            $records[] = [
                'user_id' => $this->user->id,
                'points' => rand(5, 50),
                'reason' => 'Catatan uji ke-' . $i,
                'created_at' => now()->subDays(rand(0, 25)),
            ];
        }
        DB::table('point_logs')->insert($records);

        $startTime = microtime(true);

        $response = $this->actingAs($this->user)->get('/papan-peringkat');

        $duration = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $duration, "Waktu muat {$duration}s melebihi batas 2.0 detik.");
    }
}
