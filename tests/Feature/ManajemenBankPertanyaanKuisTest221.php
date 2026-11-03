<?php

namespace Tests\Feature;

use App\Models\QuizQuestion221;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManajemenBankPertanyaanKuisTest221 extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('user');
    }

    public function test_pengguna_biasa_ditolak_403_saat_mengakses_bank_kuis(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('kuis.index'));
        $response->assertForbidden();

        $responseCreate = $this->get(route('kuis.create'));
        $responseCreate->assertForbidden();
    }

    public function test_menolak_pertanyaan_kurang_dari_10_atau_lebih_dari_500_karakter(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('kuis.store'), [
            'question' => 'Pendek?',
            'emission_weight' => 1.5,
            'options' => [
                ['label' => 'A', 'weight' => 1],
                ['label' => 'B', 'weight' => 2],
            ],
        ]);

        $response->assertSessionHasErrors('question');
    }

    public function test_menolak_pilihan_kurang_dari_dua_atau_lebih_dari_enam(): void
    {
        $this->actingAs($this->admin);

        // Hanya 1 pilihan
        $response = $this->post(route('kuis.store'), [
            'question' => 'Pertanyaan yang cukup panjang untuk kuis?',
            'emission_weight' => 1.0,
            'options' => [
                ['label' => 'Hanya satu', 'weight' => 1],
            ],
        ]);
        $response->assertSessionHasErrors('options');

        // Lebih dari 6 pilihan (7 pilihan)
        $tujuhPilihan = [];
        for ($i = 1; $i <= 7; $i++) {
            $tujuhPilihan[] = ['label' => "Pilihan $i", 'weight' => 1];
        }

        $resTujuh = $this->post(route('kuis.store'), [
            'question' => 'Pertanyaan yang cukup panjang untuk kuis tujuh opsi?',
            'emission_weight' => 1.0,
            'options' => $tujuhPilihan,
        ]);
        $resTujuh->assertSessionHasErrors('options');
    }

    public function test_menolak_jika_seluruh_bobot_pilihan_bernilai_nol(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('kuis.store'), [
            'question' => 'Pertanyaan dengan seluruh bobot nol?',
            'emission_weight' => 1.0,
            'options' => [
                ['label' => 'Pilihan A', 'weight' => 0],
                ['label' => 'Pilihan B', 'weight' => 0],
            ],
        ]);

        $response->assertSessionHasErrors('options');
    }

    public function test_pertanyaan_dengan_lima_pilihan_tersimpan_dan_terbaca_kembali_utuh(): void
    {
        $this->actingAs($this->admin);

        $limaPilihan = [
            ['label' => 'Jalan Kaki', 'weight' => 0],
            ['label' => 'Sepeda', 'weight' => 0.5],
            ['label' => 'Bus Kota', 'weight' => 1.5],
            ['label' => 'Sepeda Motor', 'weight' => 3.0],
            ['label' => 'Mobil Pribadi', 'weight' => 6.0],
        ];

        $response = $this->post(route('kuis.store'), [
            'question' => 'Bagaimana moda transportasi utamamu setiap hari?',
            'emission_weight' => 2.5000,
            'options' => $limaPilihan,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('kuis.index'));
        $response->assertSessionHas('sukses');

        $soal = QuizQuestion221::where('question', 'Bagaimana moda transportasi utamamu setiap hari?')->first();
        $this->assertNotNull($soal);
        $this->assertCount(5, $soal->options);
        $this->assertEquals('Mobil Pribadi', $soal->options[4]['label']);
        $this->assertEquals(6.0, (float) $soal->options[4]['weight']);
    }

    public function test_pertanyaan_yang_sudah_dijawab_tidak_dapat_dihapus_permanen(): void
    {
        $this->actingAs($this->admin);

        $soal = QuizQuestion221::create([
            'question' => 'Pertanyaan yang pernah dijawab oleh pengguna baru?',
            'options' => [
                ['label' => 'Ya', 'weight' => 1],
                ['label' => 'Tidak', 'weight' => 0.5],
            ],
            'emission_weight' => 1.0000,
            'is_active' => true,
        ]);

        // Simulasikan ada jawaban di onboarding_results
        DB::table('onboarding_results')->insert([
            'user_id' => $this->regularUser->id,
            'quiz_question_id' => $soal->id,
            'answer_label' => 'Ya',
            'score' => 1.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue($soal->hasBeenAnswered());

        // Coba hapus
        $response = $this->delete(route('kuis.destroy', $soal));
        $response->assertForbidden();

        // Pastikan tidak terhapus
        $this->assertDatabaseHas('quiz_questions', ['id' => $soal->id]);
    }

    public function test_status_pertanyaan_dapat_dialihkan_aktif_dan_nonaktif(): void
    {
        $this->actingAs($this->admin);

        $soal = QuizQuestion221::create([
            'question' => 'Pertanyaan saklar aktif atau nonaktif?',
            'options' => [
                ['label' => 'Opsi 1', 'weight' => 1],
                ['label' => 'Opsi 2', 'weight' => 2],
            ],
            'emission_weight' => 1.0000,
            'is_active' => true,
        ]);

        $response = $this->patch(route('kuis.status', $soal));
        $response->assertRedirect(route('kuis.index'));

        $soal->refresh();
        $this->assertFalse($soal->is_active);
    }
}
