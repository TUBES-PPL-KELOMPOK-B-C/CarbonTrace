<?php

namespace App\Services;

// Hasil satu perhitungan KF-20: angka dua desimal, nilai penuh tanpa pembulatan, dan cara hitungnya.
final readonly class HasilEmisi111
{
    public const SATUAN = 'kg CO2e';

    public function __construct(
        public string $emisi,      // dua desimal dengan titik, mis. "2.72"
        public string $nilaiPenuh, // tanpa pembulatan, supaya 0,0004 tidak hilang
        public string $rincian,    // mis. "4 kWh x 0,681 kg CO2e/kWh = 2,72 kg CO2e"
    ) {}

    // Keadaan kosong: nol, bukan null, supaya pemanggil tidak perlu menanganinya sendiri.
    public static function nol(): self
    {
        return new self('0.00', '0', 'Belum ada yang dihitung.');
    }

    // Untuk tampilan: koma sebagai pemisah desimal, mis. "2,72 kg CO2e".
    public function teks(): string
    {
        return PerhitunganEmisi111::angka($this->emisi, 2, true).' '.self::SATUAN;
    }

    public function toArray(): array
    {
        return [
            'emisi' => (float) $this->emisi,
            'emisi_teks' => $this->teks(),
            'satuan' => self::SATUAN,
            'rincian' => $this->rincian,
        ];
    }
}
