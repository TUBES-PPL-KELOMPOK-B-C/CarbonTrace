<?php

namespace Tests\Feature;

use App\Models\QuizQuestion221;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// KF-25 Pengisian Kuis Awal dan Garis Dasar (Alvin-111)
class KuisAwalTest111 extends TestCase
{
    use RefreshDatabase;

    private User $pengguna;

    /** @var array<int, QuizQuestion221> */
    private array $soal = [];

    protected function setUp(): void
    {
        parent::setUp();
        // Urutan peran sama dengan seeder (administrator = id 1): PastikanAdministrator314 juga
        // menganggap role_id 1 sebagai administrator.
        Role::findOrCreate('administrator', 'web');
        Role::findOrCreate('pengguna', 'web');
        $this->pengguna = User::factory()->create(['password' => 'rahasia123']);

        $this->soal[] = QuizQuestion221::create(['question' => 'Bagaimana kamu bepergian?', 'emission_weight' => 2, 'is_active' => true,
            'options' => [['label' => 'Jalan kaki', 'weight' => 0], ['label' => 'Motor', 'weight' => 3]]]);
        $this->soal[] = QuizQuestion221::create(['question' => 'Berapa jam AC menyala?', 'emission_weight' => 1.5, 'is_active' => true,
            'options' => [['label' => 'Tidak pakai', 'weight' => 0], ['label' => 'Lebih dari 8 jam', 'weight' => 4]]]);
        QuizQuestion221::create(['question' => 'Soal nonaktif', 'emission_weight' => 9, 'is_active' => false,
            'options' => [['label' => 'A', 'weight' => 9], ['label' => 'B', 'weight' => 9]]]);
    }

    private function jawab(int $indeksSoal, int $pilihan)
    {
        return $this->post(route('kuis-awal.jawab'), ['soal_id' => $this->soal[$indeksSoal]->id, 'pilihan' => $pilihan]);
    }

    public function test_masuk_pertama_kali_diarahkan_ke_kuis_awal_tetapi_admin_tidak(): void
    {
        $this->post(route('auth.proses'), ['email' => $this->pengguna->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('kuis-awal.mulai'));
        $this->post(route('auth.keluar'));

        $admin = User::factory()->create(['password' => 'rahasia123']);
        $admin->assignRole(Role::findOrCreate('administrator', 'web'));
        $this->post(route('auth.proses'), ['email' => $admin->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard.index'));
    }

    public function test_satu_soal_per_layar_hanya_soal_aktif_dan_jawaban_wajib(): void
    {
        $this->actingAs($this->pengguna)->get(route('kuis-awal.mulai'))
            ->assertOk()->assertSee('Soal 1 dari 2')->assertSee('Bagaimana kamu bepergian?')
            ->assertDontSee('Berapa jam AC menyala?')->assertDontSee('Soal nonaktif');

        $this->post(route('kuis-awal.jawab'), ['soal_id' => $this->soal[0]->id])
            ->assertSessionHasErrors(['pilihan' => 'Pilih salah satu jawaban dulu.']);
        $this->jawab(0, 7)->assertSessionHasErrors('pilihan');
        $this->assertDatabaseCount('onboarding_results', 0);
    }

    public function test_menjawab_semua_soal_menghasilkan_garis_dasar(): void
    {
        $this->actingAs($this->pengguna);
        $this->jawab(0, 1)->assertRedirect(route('kuis-awal.mulai', ['soal' => 2]));
        $this->jawab(1, 1)->assertRedirect(route('kuis-awal.hasil'))
            ->assertSessionHas('sukses', 'Kuis awal selesai. Garis dasar emisimu 12,00 kg CO2e per hari.');

        // 3 x 2 + 4 x 1,5 = 12
        $this->get(route('kuis-awal.hasil'))->assertOk()->assertSee('12,00')->assertSee('Motor');
        $this->get(route('dashboard.index'))->assertSee('Garis dasar kuis awal 12,00 kg per hari')
            ->assertDontSee('Isi kuis awal untuk mengetahui garis dasar emisimu');

        // Selesai kuis tercatat sekali di lonceng, bukan sekali per soal.
        $this->assertSame(1, $this->pengguna->notifications()->count());
    }

    public function test_mengulang_kuis_tidak_membuat_baris_ganda(): void
    {
        $this->actingAs($this->pengguna);
        $this->jawab(0, 1);
        $this->jawab(1, 1);
        $this->get(route('kuis-awal.mulai', ['soal' => 1]))->assertOk()->assertSee('value="1" checked', false);
        $this->jawab(0, 0);
        $this->jawab(1, 0);

        $this->assertDatabaseCount('onboarding_results', 2);
        $this->get(route('kuis-awal.hasil'))->assertSee('0,00');
    }

    public function test_melewati_kuis_tidak_diarahkan_lagi_dan_dashboard_menawarkan_kuis(): void
    {
        $this->actingAs($this->pengguna)->get(route('dashboard.index'))->assertSee('Isi kuis awal untuk mengetahui garis dasar emisimu');

        $this->post(route('kuis-awal.lewati'))->assertRedirect(route('dashboard.index'));
        $this->assertNotNull(DB::table('users')->where('id', $this->pengguna->id)->value('quiz_skipped_at'));
        $this->post(route('auth.keluar'));

        $this->post(route('auth.proses'), ['email' => $this->pengguna->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard.index'));
    }

    public function test_soal_yang_dinonaktifkan_tidak_dihitung_tetapi_jawabannya_tetap_tersimpan(): void
    {
        $this->actingAs($this->pengguna);
        $this->jawab(0, 1);
        $this->jawab(1, 1);
        $this->soal[1]->update(['is_active' => false]);

        $this->get(route('kuis-awal.hasil'))->assertSee('6,00');
        $this->assertDatabaseCount('onboarding_results', 2);
    }

    public function test_ringkasan_hanya_untuk_admin_dan_menampilkan_sebaran(): void
    {
        $this->actingAs($this->pengguna);
        $this->jawab(0, 1);
        $this->jawab(1, 0);
        $this->get(route('kuis-awal.ringkasan'))->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('administrator', 'web'));
        $this->actingAs($admin)->get(route('kuis-awal.ringkasan'))->assertOk()
            ->assertSeeInOrder(['Pengguna aktif', '1', 'Sudah mengisi', '1'])
            ->assertSee('Bagaimana kamu bepergian?')
            ->assertSee($this->pengguna->email)
            ->assertSee('6,00');
    }

    public function test_tanpa_soal_aktif_kuis_tidak_dipaksakan(): void
    {
        QuizQuestion221::query()->update(['is_active' => false]);

        $this->actingAs($this->pengguna)->get(route('kuis-awal.mulai'))->assertRedirect(route('dashboard.index'));
        $this->get(route('dashboard.index'))->assertDontSee('Isi kuis awal untuk mengetahui garis dasar emisimu');
    }
}
