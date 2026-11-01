<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardTest111 extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-15 10:00:00');
    }

    // Tabel milik modul lain belum ada di repo, jadi tes membuat salinan skemanya sesuai README
    // pemiliknya: activities (Hadi), targets (Alvin), point_logs (Man), emission_factors dan
    // categories (Renaya), announcements (Tio).
    private function siapkanTabel(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('base_unit');
            $t->boolean('is_active')->default(true);
        });
        Schema::create('emission_factors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id');
            $t->string('name');
            $t->string('unit');
            $t->decimal('factor_value', 12, 4);
            $t->boolean('is_active')->default(true);
        });
        Schema::create('activities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id');
            $t->foreignId('emission_factor_id');
            $t->decimal('amount', 12, 4);
            $t->decimal('emission', 12, 4);
            $t->date('activity_date');
            $t->string('note')->default('');
            $t->timestamps();
        });
        Schema::create('targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id');
            $t->string('period_start');
            $t->string('period_end');
            $t->decimal('emission_limit', 12, 4);
            $t->timestamps();
        });
        Schema::create('point_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id');
            $t->integer('points')->default(0);
            $t->string('reason');
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('announcements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id');
            $t->string('title');
            $t->text('content')->nullable();
            $t->date('start_date');
            $t->date('end_date');
            $t->timestamps();
        });

        $listrik = DB::table('categories')->insertGetId(['name' => 'Listrik', 'base_unit' => 'kWh']);
        DB::table('emission_factors')->insert(['id' => 1, 'category_id' => $listrik, 'name' => 'Listrik PLN', 'unit' => 'kWh', 'factor_value' => 0.681]);
    }

    private function catat(User $pengguna, string $tanggal, float $emisi, float $jumlah = 1): void
    {
        DB::table('activities')->insert([
            'user_id' => $pengguna->id, 'emission_factor_id' => 1, 'amount' => $jumlah,
            'emission' => $emisi, 'activity_date' => $tanggal, 'note' => '',
        ]);
    }

    public function test_tamu_dipantulkan_ke_halaman_masuk(): void
    {
        $this->get(route('dashboard.index'))->assertRedirect(route('auth.masuk'));
    }

    public function test_masuk_langsung_diarahkan_ke_dashboard(): void
    {
        $pengguna = User::factory()->create(['password' => 'rahasia123']);

        $this->post(route('auth.proses'), ['email' => $pengguna->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard.index'));
    }

    public function test_pengguna_baru_tanpa_data_melihat_halaman_utuh_dengan_angka_nol_dan_ajakan(): void
    {
        // Belum ada satu pun tabel modul lain
        $this->actingAs(User::factory()->create(['name' => 'Alvin Hibatullah']))
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Halo, Alvin.')
            ->assertSeeInOrder(['Emisi hari ini', '0,00', 'Emisi bulan ini', '0,00', 'Sisa kuota target', 'Tetapkan Target', 'EcoPoint', '0'])
            ->assertSee('Catat aktivitas pertamamu untuk melihat angkanya.')
            ->assertSee('Belum ada aktivitas.')
            ->assertDontSee('Gagal memuat');
    }

    public function test_keempat_kartu_cocok_dengan_data_uji(): void
    {
        $this->siapkanTabel();
        $alvin = User::factory()->create();
        $lain = User::factory()->create();

        $this->catat($alvin, '2026-11-15', 2.5);   // hari ini
        $this->catat($alvin, '2026-11-15', 1.25);  // hari ini
        $this->catat($alvin, '2026-11-03', 10);    // bulan ini
        $this->catat($alvin, '2026-10-31', 99);    // bulan lalu: tidak dihitung
        $this->catat($lain, '2026-11-15', 500);    // milik orang lain: tidak dihitung
        DB::table('targets')->insert(['user_id' => $alvin->id, 'period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'emission_limit' => 50]);
        DB::table('point_logs')->insert([
            ['user_id' => $alvin->id, 'points' => 40, 'reason' => 'tantangan'],
            ['user_id' => $alvin->id, 'points' => 25, 'reason' => 'lencana'],
            ['user_id' => $lain->id, 'points' => 999, 'reason' => 'lain'],
        ]);

        $this->actingAs($alvin)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'Emisi hari ini', '3,75',
                'Emisi bulan ini', '13,75',
                'Sisa kuota target', '36,25', 'Tersisa dari batas 50,00 kg',
                'EcoPoint', '65',
            ])
            ->assertDontSee('Catat aktivitas pertamamu untuk melihat angkanya.');
    }

    public function test_target_terlampaui_ditandai(): void
    {
        $this->siapkanTabel();
        $alvin = User::factory()->create();
        $this->catat($alvin, '2026-11-10', 60);
        DB::table('targets')->insert(['user_id' => $alvin->id, 'period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'emission_limit' => 50]);

        $this->actingAs($alvin)->get(route('dashboard.index'))
            ->assertSeeInOrder(['Sisa kuota target', '10,00', 'Terlampaui dari batas 50,00 kg']);
    }

    public function test_lima_aktivitas_terbaru_dan_pengumuman_yang_berlaku(): void
    {
        $this->siapkanTabel();
        $alvin = User::factory()->create();
        foreach (range(1, 7) as $hari) {
            $this->catat($alvin, sprintf('2026-11-%02d', $hari), $hari, $hari);
        }
        DB::table('announcements')->insert([
            ['user_id' => $alvin->id, 'title' => 'Pengumuman lama', 'start_date' => '2026-10-01', 'end_date' => '2026-10-31'],
            ['user_id' => $alvin->id, 'title' => 'Pekan Hemat Energi', 'start_date' => '2026-11-10', 'end_date' => '2026-11-20'],
        ]);

        $tanggapan = $this->actingAs($alvin)->get(route('dashboard.index'))->assertOk();

        $tanggapan->assertSee('Pekan Hemat Energi')->assertDontSee('Pengumuman lama');
        $tanggapan->assertSeeInOrder(['7 Nov 2026', '6 Nov 2026', '5 Nov 2026', '4 Nov 2026', '3 Nov 2026']);
        $tanggapan->assertDontSee('2 Nov 2026')->assertSee('Listrik PLN')->assertSee('Listrik');
    }

    public function test_satu_bagian_gagal_tidak_merusak_bagian_lain(): void
    {
        // Kueri ke tabel targets sengaja digagalkan; bagian lain harus tetap tampil
        $this->siapkanTabel();
        DB::listen(function ($kueri) {
            if (str_contains($kueri->sql, 'from "targets"')) {
                throw new \RuntimeException('Uji: tabel target sedang bermasalah');
            }
        });

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSeeInOrder(['Emisi hari ini', '0,00', 'Sisa kuota target', 'Gagal memuat', 'Coba lagi', 'EcoPoint']);
    }

    public function test_jumlah_kueri_di_bawah_sepuluh_walau_datanya_banyak(): void
    {
        $this->siapkanTabel();
        $alvin = User::factory()->create();
        DB::table('activities')->insert(array_map(fn ($i) => [
            'user_id' => $alvin->id, 'emission_factor_id' => 1, 'amount' => 1,
            'emission' => 0.5, 'activity_date' => '2026-11-'.str_pad((string) ($i % 15 + 1), 2, '0', STR_PAD_LEFT), 'note' => '',
        ], range(1, 500)));
        DB::table('targets')->insert(['user_id' => $alvin->id, 'period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'emission_limit' => 500]);

        DB::enableQueryLog();
        $this->actingAs($alvin)->get(route('dashboard.index'))->assertOk();

        $this->assertLessThan(10, count(DB::getQueryLog()));
    }
}
