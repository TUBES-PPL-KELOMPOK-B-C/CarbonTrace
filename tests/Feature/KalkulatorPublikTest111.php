<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KalkulatorPublikTest111 extends TestCase
{
    use RefreshDatabase;

    // Tabel categories dan emission_factors milik PBI Renaya (154) belum ada di repo,
    // jadi tes ini membuat salinan skemanya sendiri sesuai README.
    private function siapkanFaktor(): array
    {
        // Dibuat hanya bila migrasinya belum ada di repo. Saat ini keduanya dibuat migrasi stub
        // Modul 2 (Hadi), jadi data uji hanya mengisi kolom yang ada di skema README maupun stub.
        if (! Schema::hasTable('categories')) Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('base_unit');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        if (! Schema::hasTable('emission_factors')) Schema::create('emission_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('unit');
            $table->decimal('factor_value', 12, 4);
            $table->string('reference_url');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $listrik = DB::table('categories')->insertGetId(['name' => 'Listrik']);
        $sampah = DB::table('categories')->insertGetId(['name' => 'Sampah']);

        return [
            'listrik' => DB::table('emission_factors')->insertGetId([
                'category_id' => $listrik, 'name' => 'Listrik PLN', 'unit' => 'kWh',
                'factor_value' => 0.681,
            ]),
            'sampah' => DB::table('emission_factors')->insertGetId([
                'category_id' => $sampah, 'name' => 'Sampah organik', 'unit' => 'kg',
                'factor_value' => 1,
            ]),
            'arsip' => DB::table('emission_factors')->insertGetId([
                'category_id' => $listrik, 'name' => 'Listrik lama', 'unit' => 'kWh',
                'factor_value' => 0.9, 'is_active' => false,
            ]),
        ];
    }

    public function test_tanpa_faktor_emisi_halaman_menyatakan_belum_siap(): void
    {
        $this->get(route('kalkulator.index'))
            ->assertOk()
            ->assertSee('Kalkulator belum siap');
    }

    public function test_tamu_bisa_membuka_kalkulator_dan_melihat_kategori(): void
    {
        $this->siapkanFaktor();

        $this->get(route('kalkulator.index'))
            ->assertOk()
            ->assertSee('Kalkulator Jejak Karbon')
            ->assertSee('Listrik PLN (kWh)')
            ->assertDontSee('Listrik lama')
            ->assertSee('Hasilmu akan muncul di sini.');
    }

    public function test_api_menghitung_angka_yang_benar_tanpa_akun(): void
    {
        $faktor = $this->siapkanFaktor();

        $this->postJson(route('kalkulator.api'), ['emission_factor_id' => $faktor['listrik'], 'amount' => 4])
            ->assertOk()
            ->assertJson([
                'emisi' => 2.72,
                'emisi_teks' => '2,72 kg CO2e',
                'satuan' => 'kg CO2e',
                'rincian' => '4 kWh x 0,681 kg CO2e/kWh = 2,72 kg CO2e',
                'perbandingan' => 'Setara 22,7 km berkendara motor',
            ]);

        $this->assertGuest();
    }

    public function test_menghitung_tidak_menambah_baris_di_basis_data(): void
    {
        $faktor = $this->siapkanFaktor();
        $sebelum = collect(['users', 'categories', 'emission_factors'])->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()]);

        $this->postJson(route('kalkulator.api'), ['emission_factor_id' => $faktor['sampah'], 'amount' => '1,5'])
            ->assertOk()
            ->assertJsonPath('emisi_teks', '1,50 kg CO2e');

        $sebelum->each(fn ($jumlah, $tabel) => $this->assertDatabaseCount($tabel, $jumlah));
    }

    public function test_formulir_tanpa_javascript_menampilkan_hasil_setelah_dimuat_ulang(): void
    {
        $faktor = $this->siapkanFaktor();

        $this->followingRedirects()
            ->post(route('kalkulator.hitung'), ['emission_factor_id' => $faktor['listrik'], 'amount' => '4'])
            ->assertOk()
            ->assertSee('2,72 kg CO2e')
            ->assertSee('Setara 22,7 km berkendara motor');
    }

    public function test_masukan_tidak_valid_ditolak_dengan_pesan_per_kolom(): void
    {
        $faktor = $this->siapkanFaktor();

        $this->postJson(route('kalkulator.api'), ['emission_factor_id' => $faktor['arsip'], 'amount' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'emission_factor_id' => 'Jenis aktivitas ini tidak tersedia. Pilih yang lain.',
                'amount' => 'Jumlah harus lebih besar dari 0.',
            ]);

        $this->postJson(route('kalkulator.api'), ['amount' => 100001])
            ->assertJsonValidationErrors(['emission_factor_id', 'amount' => 'Jumlah maksimal 100.000.']);
    }

    public function test_permintaan_ke_31_dalam_semenit_ditolak_dengan_pesan_ramah(): void
    {
        $faktor = $this->siapkanFaktor();
        $data = ['emission_factor_id' => $faktor['sampah'], 'amount' => 1];

        for ($i = 0; $i < 30; $i++) {
            $this->postJson(route('kalkulator.api'), $data)->assertOk();
        }

        $this->postJson(route('kalkulator.api'), $data)
            ->assertStatus(429)
            ->assertJson(['pesan' => 'Terlalu banyak percobaan. Tunggu satu menit.']);

        $this->from(route('kalkulator.index'))
            ->post(route('kalkulator.hitung'), $data)
            ->assertRedirect(route('kalkulator.index'))
            ->assertSessionHas('galat_kalkulator', 'Terlalu banyak percobaan. Tunggu satu menit.');
    }

    public function test_batas_kalkulator_tidak_menghabiskan_jatah_masuk(): void
    {
        $faktor = $this->siapkanFaktor();

        for ($i = 0; $i < 6; $i++) {
            $this->postJson(route('kalkulator.api'), ['emission_factor_id' => $faktor['sampah'], 'amount' => 1]);
        }

        $this->postJson(route('auth.api.masuk'), ['email' => 'tidak@ada.id', 'password' => 'salah-sandi'])
            ->assertStatus(401);
    }
}
