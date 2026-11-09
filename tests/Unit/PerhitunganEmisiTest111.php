<?php

namespace Tests\Unit;

use App\Exceptions\FaktorEmisiTidakAktif111;
use App\Services\PerhitunganEmisi111;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PerhitunganEmisiTest111 extends TestCase
{
    private PerhitunganEmisi111 $hitung;

    protected function setUp(): void
    {
        $this->hitung = new PerhitunganEmisi111;
    }

    private function faktor(string $kategori, string $unit, string $nilai, bool $aktif = true): object
    {
        return (object) [
            'name' => "Faktor {$kategori}",
            'unit' => $unit,
            'factor_value' => $nilai,
            'is_active' => $aktif,
            'category_name' => $kategori,
        ];
    }

    public function test_ac_900_watt_8_jam_di_rumah_4_orang(): void
    {
        // (900 x 8) / 1000 = 7,2 kWh x 0,681 = 4,9032 / 4 = 1,2258
        $hasil = $this->hitung->hitungListrik($this->faktor('Listrik', 'kWh', '0.6810'), 900, 8, 4);

        $this->assertSame('1.23', $hasil->emisi);
        $this->assertSame('1.2258', $hasil->nilaiPenuh);
        $this->assertSame('(900 W x 8 jam) / 1000 = 7,2 kWh x 0,681 kg CO2e/kWh / 4 penghuni = 1,23 kg CO2e', $hasil->rincian);
    }

    public function test_pengguna_tanpa_data_rumah_tangga_tetap_dapat_angka_penuh(): void
    {
        $hasil = $this->hitung->hitungListrik($this->faktor('Listrik', 'kWh', '0.681'), 900, 8);

        $this->assertSame('4.90', $hasil->emisi);
    }

    public function test_penghuni_nol_dari_data_lama_dianggap_satu(): void
    {
        $hasil = $this->hitung->hitungListrik($this->faktor('Listrik', 'kWh', '0.681'), 900, 8, 0);

        $this->assertSame('4.90', $hasil->emisi);
    }

    public function test_hitung_umum_listrik_dibagi_penghuni_kategori_lain_tidak(): void
    {
        $this->assertSame('2.72', $this->hitung->hitung($this->faktor('Listrik', 'kWh', '0.681'), 4)->emisi);
        $this->assertSame('1.36', $this->hitung->hitung($this->faktor('Listrik', 'kWh', '0.681'), 4, 2)->emisi);
        $this->assertSame('2.40', $this->hitung->hitung($this->faktor('Transportasi', 'km', '0.12'), 20, 2)->emisi);
    }

    public function test_makanan_porsi_dikonversi_ke_kilogram(): void
    {
        // 2 porsi x 0,15 kg = 0,3 kg x 27 = 8,1
        $hasil = $this->hitung->hitungMakanan($this->faktor('Makanan', 'kg', '27'), 2, '0.15');

        $this->assertSame('8.10', $hasil->emisi);
        $this->assertSame('2 porsi x 0,15 kg = 0,3 kg x 27 kg CO2e/kg = 8,10 kg CO2e', $hasil->rincian);
    }

    public function test_transportasi_kilometer_dikali_langsung(): void
    {
        $hasil = $this->hitung->hitungTransportasi($this->faktor('Transportasi', 'km', '0.12'), 2);

        $this->assertSame('0.24', $hasil->emisi);
        $this->assertSame('2 km x 0,12 kg CO2e/km = 0,24 kg CO2e', $hasil->rincian);
    }

    public function test_sampah_satu_setengah_kilo_tidak_terpotong(): void
    {
        $hasil = $this->hitung->hitungSampah($this->faktor('Sampah', 'kg', '1'), '1.5');

        $this->assertSame('1.50', $hasil->emisi);
    }

    public function test_hasil_sangat_kecil_jadi_nol_tapi_nilai_penuh_tersimpan(): void
    {
        $hasil = $this->hitung->hitungSampah($this->faktor('Sampah', 'kg', '0.0004'), 1);

        $this->assertSame('0.00', $hasil->emisi);
        $this->assertSame('0.0004', $hasil->nilaiPenuh);
        $this->assertStringContainsString('(nilai penuh 0,0004)', $hasil->rincian);
    }

    public function test_jumlah_kosong_mengembalikan_nol_bukan_null(): void
    {
        $hasil = $this->hitung->hitung($this->faktor('Sampah', 'kg', '1'), null);

        $this->assertSame('0.00', $hasil->emisi);
        $this->assertSame('0,00 kg CO2e', $hasil->teks());
    }

    public function test_jumlah_nol_atau_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->hitung->hitungTransportasi($this->faktor('Transportasi', 'km', '0.12'), -3);
    }

    public function test_faktor_yang_diarsipkan_melempar_pengecualian_bernama_jelas(): void
    {
        $this->expectException(FaktorEmisiTidakAktif111::class);

        $this->hitung->hitung($this->faktor('Listrik', 'kWh', '0.681', aktif: false), 4);
    }
}
