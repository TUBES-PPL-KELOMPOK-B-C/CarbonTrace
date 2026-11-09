<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\EmissionFactor;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\EmissionCalculatorService221;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class EmissionCalculatorService221Test extends TestCase
{
    use RefreshDatabase;

    protected EmissionCalculatorService221 $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmissionCalculatorService221();
    }

    public function test_menghitung_emisi_standar_dengan_benar(): void
    {
        $cat = Category::create(['name' => 'Transportasi', 'code' => 'transportasi']);
        $factor = EmissionFactor::create([
            'category_id' => $cat->id,
            'name' => 'Motor Bensin',
            'factor_value' => 0.1200,
            'unit' => 'km',
            'is_active' => true,
        ]);

        // 12.5 km x 0.12 = 1.50 kg CO2e
        $hasil = $this->service->calculate($factor, 12.5);

        $this->assertEquals(1.50, $hasil['emisi']);
        $this->assertEquals(1.5000, $hasil['emisi_persis']);
        $this->assertEquals('kg CO2e', $hasil['satuan']);
    }

    public function test_kategori_listrik_dibagi_household_size_pengguna(): void
    {
        $user = User::factory()->create();
        UserSetting::create(['user_id' => $user->id, 'household_size' => 4]);

        $cat = Category::create(['name' => 'Listrik & Energi', 'code' => 'listrik']);
        $factor = EmissionFactor::create([
            'category_id' => $cat->id,
            'name' => 'Listrik PLN',
            'factor_value' => 0.8000,
            'unit' => 'kWh',
            'is_active' => true,
        ]);

        // 10 kWh x 0.80 = 8.0 kg CO2e, dibagi 4 orang = 2.00 kg CO2e
        $hasil = $this->service->calculate($factor, 10.0, $user);

        $this->assertEquals(2.00, $hasil['emisi']);
        $this->assertEquals(2.0000, $hasil['emisi_persis']);
    }

    public function test_menolak_jumlah_nol_atau_negatif(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $cat = Category::create(['name' => 'Transportasi', 'code' => 'transportasi']);
        $factor = EmissionFactor::create([
            'category_id' => $cat->id,
            'name' => 'Motor Bensin',
            'factor_value' => 0.1200,
            'unit' => 'km',
            'is_active' => true,
        ]);

        $this->service->calculate($factor, 0);
    }

    public function test_menolak_faktor_emisi_yang_tidak_aktif(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $cat = Category::create(['name' => 'Transportasi', 'code' => 'transportasi']);
        $factor = EmissionFactor::create([
            'category_id' => $cat->id,
            'name' => 'Motor Tua',
            'factor_value' => 0.5000,
            'unit' => 'km',
            'is_active' => false,
        ]);

        $this->service->calculate($factor, 10);
    }
}
