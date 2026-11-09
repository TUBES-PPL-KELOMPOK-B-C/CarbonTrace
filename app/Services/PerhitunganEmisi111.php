<?php

namespace App\Services;

use App\Exceptions\FaktorEmisiTidakAktif111;
use InvalidArgumentException;

// KF-20 Perhitungan Emisi Otomatis: satu-satunya tempat rumus emisi ditulis. Dipakai kalkulator
// publik (KF-24) dan pencatatan aktivitas (PBI-04) supaya angkanya selalu sama.
// Hanya menerima angka dan mengembalikan angka; tidak membaca atau menulis tabel activities.
// Faktor emisi boleh berupa model Eloquent atau baris query builder, asalkan punya
// name, unit, factor_value, is_active, dan nama kategori (category_name atau category->name).
// Semua hitungan memakai bcmath supaya 1,5 kg tidak pernah terpotong menjadi 1 kg.
class PerhitunganEmisi111
{
    private const SKALA = 6;

    // Jumlah sudah dalam satuan faktornya (kWh, km, kg). Untuk kategori listrik hasilnya dibagi
    // jumlah penghuni rumah; null berarti tanpa pembagian (kalkulator publik, atau data rumah
    // tangga belum diisi).
    public function hitung(object $faktor, string|int|float|null $jumlah, ?int $penghuni = null): HasilEmisi111
    {
        if ($jumlah === null || $jumlah === '') {
            return HasilEmisi111::nol();
        }

        [$penuh, $rincian] = $this->kali($faktor, $this->desimal($jumlah), $faktor->unit);

        return $this->selesai($penuh, $rincian, $this->apakahListrik($faktor) ? $penghuni : null);
    }

    // kWh = (daya watt x jam) / 1000, dikalikan faktor, lalu dibagi jumlah penghuni rumah.
    public function hitungListrik(object $faktor, string|int|float $dayaWatt, string|int|float $jam, ?int $penghuni = null): HasilEmisi111
    {
        $kwh = bcdiv(bcmul($this->desimal($dayaWatt), $this->desimal($jam), self::SKALA), '1000', self::SKALA);
        [$penuh, $rincian] = $this->kali($faktor, $kwh, 'kWh');

        return $this->selesai($penuh, '('.self::angka($dayaWatt).' W x '.self::angka($jam).' jam) / 1000 = '.$rincian, $penghuni);
    }

    // kilogram = porsi x kg_conversion, lalu dikalikan faktor.
    public function hitungMakanan(object $faktor, string|int|float $porsi, string|int|float $kgPerPorsi): HasilEmisi111
    {
        $kg = bcmul($this->desimal($porsi), $this->desimal($kgPerPorsi), self::SKALA);
        [$penuh, $rincian] = $this->kali($faktor, $kg, 'kg');

        return $this->selesai($penuh, self::angka($porsi).' porsi x '.self::angka($kgPerPorsi).' kg = '.$rincian);
    }

    // kilometer dikalikan faktor secara langsung.
    public function hitungTransportasi(object $faktor, string|int|float $km): HasilEmisi111
    {
        return $this->selesai(...$this->kali($faktor, $this->desimal($km), 'km'));
    }

    // kilogram dikalikan faktor secara langsung.
    public function hitungSampah(object $faktor, string|int|float $kg): HasilEmisi111
    {
        return $this->selesai(...$this->kali($faktor, $this->desimal($kg), 'kg'));
    }

    // Angka gaya Indonesia: koma untuk desimal, titik untuk ribuan, tanpa nol di belakang
    // kecuali $tetap, mis. 0.6810 -> "0,681" dan 2.7 -> "2,70" bila $tetap.
    public static function angka(string|int|float $nilai, int $desimal = 4, bool $tetap = false): string
    {
        $teks = number_format((float) $nilai, $desimal, ',', '.');

        return $tetap || ! str_contains($teks, ',') ? $teks : rtrim(rtrim($teks, '0'), ',');
    }

    /** @return array{0: string, 1: string} nilai penuh dan rincian sejauh ini */
    private function kali(object $faktor, string $jumlah, string $satuan): array
    {
        if (! (bool) data_get($faktor, 'is_active', true)) {
            throw new FaktorEmisiTidakAktif111((string) data_get($faktor, 'name', ''));
        }

        $nilaiFaktor = $this->desimal($faktor->factor_value);

        return [
            bcmul($jumlah, $nilaiFaktor, self::SKALA),
            self::angka($jumlah).' '.$satuan.' x '.self::angka($nilaiFaktor).' '.HasilEmisi111::SATUAN.'/'.$satuan,
        ];
    }

    private function selesai(string $penuh, string $rincian, ?int $penghuni = null): HasilEmisi111
    {
        // Data lama bisa berisi 0 penghuni: perlakukan sebagai satu, jangan membagi dengan nol.
        if ($penghuni !== null && $penghuni > 1) {
            $penuh = bcdiv($penuh, (string) $penghuni, self::SKALA);
            $rincian .= ' / '.$penghuni.' penghuni';
        }

        // Semua nilai positif, jadi menambah 0,005 lalu memotong = pembulatan setengah ke atas.
        $emisi = bcadd($penuh, '0.005', 2);
        $rincian .= ' = '.self::angka($emisi, 2, true).' '.HasilEmisi111::SATUAN;

        if (bccomp($emisi, '0', 2) === 0 && bccomp($penuh, '0', self::SKALA) === 1) {
            $rincian .= ' (nilai penuh '.self::angka($penuh, self::SKALA).')';
        }

        return new HasilEmisi111($emisi, rtrim(rtrim($penuh, '0'), '.'), $rincian);
    }

    private function apakahListrik(object $faktor): bool
    {
        $kategori = data_get($faktor, 'category_name') ?? data_get($faktor, 'category.name') ?? '';

        return str_contains(strtolower((string) $kategori), 'listrik');
    }

    // Ubah masukan menjadi string desimal untuk bcmath, dan tolak nilai nol atau negatif.
    private function desimal(string|int|float $nilai): string
    {
        $teks = trim((string) $nilai);

        // bcmath tidak mengenal notasi ilmiah seperti 1e3 atau 1.0E-5.
        if (is_float($nilai) || (is_numeric($teks) && stripos($teks, 'e') !== false)) {
            $teks = sprintf('%.'.self::SKALA.'F', (float) $teks);
        }

        if (! is_numeric($teks) || bccomp($teks, '0', self::SKALA) <= 0) {
            throw new InvalidArgumentException('Jumlah wajib berupa angka lebih besar dari 0.');
        }

        return $teks;
    }
}
