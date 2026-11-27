<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// PBI-12: seluruh angka laporan disiapkan di sini, dipakai bersama oleh pratinjau, tampilan di layar,
// dan nanti berkas PDF. Tidak ada angka yang disimpan; koreksi catatan langsung tercermin.
class RingkasanLaporan022
{
    private const PALET = ['#10B981', '#38BDF8', '#F59E0B', '#A3E635', '#F43F5E', '#A78BFA'];

    private const WARNA_DIHAPUS = '#8CA099';

    private const BARIS_PER_HALAMAN = 35;

    // Kategori yang bisa dipilih di formulir.
    public static function kategori(): Collection
    {
        return DB::table('categories')->orderBy('id')->get();
    }

    // Memilih semua kategori disimpan sebagai lingkup kosong, supaya kategori yang ditambahkan admin
    // belakangan ikut masuk laporan.
    /** @return int[] */
    public static function rapikanLingkup(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids);

        return count($ids) >= DB::table('categories')->count() ? [] : $ids;
    }

    public function untuk(int $penggunaId, string $mulai, string $selesai, array $lingkup = []): array
    {
        $baris = DB::table('activities as a')
            ->join('emission_factors as f', 'f.id', '=', 'a.emission_factor_id')
            ->where('a.user_id', $penggunaId)
            // Batas akhir sampai 23.59.59 supaya hari terakhir terhitung walau tanggal tersimpan beserta jam.
            ->whereBetween('a.activity_date', [$mulai, $selesai.' 23:59:59'])
            ->when($lingkup, fn ($q) => $q->whereIn('f.category_id', $lingkup))
            ->groupBy('a.activity_date', 'f.category_id')
            ->selectRaw('a.activity_date as tanggal, f.category_id as kategori_id, SUM(a.emission) as emisi, COUNT(*) as jumlah')
            ->get();

        $perTanggal = [];
        $perKategori = [];
        foreach ($baris as $b) {
            $tanggal = substr((string) $b->tanggal, 0, 10);
            $perTanggal[$tanggal] = ($perTanggal[$tanggal] ?? 0) + (float) $b->emisi;
            $perKategori[$b->kategori_id] = ($perKategori[$b->kategori_id] ?? 0) + (float) $b->emisi;
        }

        $awal = Carbon::createFromFormat('!Y-m-d', $mulai);
        $hari = (int) $awal->diffInDays(Carbon::createFromFormat('!Y-m-d', $selesai)) + 1;
        $tren = [];
        for ($i = 0; $i < $hari; $i++) {
            $tanggal = $awal->copy()->addDays($i)->toDateString();
            $tren[] = ['tanggal' => $tanggal, 'emisi' => round($perTanggal[$tanggal] ?? 0, 2)];
        }

        $total = (float) array_sum($perTanggal);

        return [
            'total' => round($total, 2),
            'rata_harian' => round($total / $hari, 2),
            'per_kategori' => $this->rincianKategori($perKategori, $lingkup, $total),
            'tren' => $tren,
            'perkiraan_halaman' => 1 + (int) ceil(count($perTanggal) / self::BARIS_PER_HALAMAN),
            'jumlah_hari' => $hari,
            'jumlah_catatan' => (int) $baris->sum('jumlah'),
        ];
    }

    private function rincianKategori(array $perKategori, array $lingkup, float $total): array
    {
        $info = [];
        foreach (self::kategori()->values() as $i => $k) {
            // Kolom color ada di skema Renaya, belum ada di tabel sementara.
            $info[$k->id] = [$k->name, ($k->color ?? null) ?: self::PALET[$i % count(self::PALET)]];
        }

        // Kategori dalam lingkup yang sudah dihapus admin tetap disebut, dengan angka nol.
        foreach (array_diff($lingkup, array_keys($info)) as $hilang) {
            $perKategori[$hilang] ??= 0.0;
        }

        arsort($perKategori);
        $persen = $this->persenGenap($perKategori, $total);

        return collect($perKategori)->map(fn (float $emisi, int $id) => [
            'id' => $id,
            'nama' => $info[$id][0] ?? 'Kategori dihapus',
            'emisi' => round($emisi, 2),
            'persen' => $persen[$id],
            'warna' => $info[$id][1] ?? self::WARNA_DIHAPUS,
        ])->values()->all();
    }

    // Persentase satu desimal yang jumlahnya tepat 100: sisa pembulatan diberikan ke pecahan terbesar.
    private function persenGenap(array $nilai, float $total): array
    {
        if ($total <= 0) {
            return array_map(fn () => 0.0, $nilai);
        }

        $permil = array_map(fn (float $n) => $n / $total * 1000, $nilai);
        $bulat = array_map('floor', $permil);
        $pecahan = [];
        foreach ($permil as $id => $p) {
            $pecahan[$id] = $p - $bulat[$id];
        }
        arsort($pecahan);

        foreach (array_slice(array_keys($pecahan), 0, (int) round(1000 - array_sum($bulat))) as $id) {
            $bulat[$id]++;
        }

        return array_map(fn (float $b) => $b / 10, $bulat);
    }
}
