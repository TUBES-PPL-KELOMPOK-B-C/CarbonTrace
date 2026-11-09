<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

// KF-21 Dashboard: seluruh angka dashboard dihitung di sini, bukan di Blade.
// Hanya MEMBACA tabel milik modul lain (activities, targets, point_logs, emission_factors,
// categories, announcements). Tabel yang belum ada dianggap kosong, sehingga pengguna hari
// pertama tetap melihat halaman utuh dengan angka nol. Satu bagian yang gagal hanya membuat
// bagian itu bernilai null (ditampilkan "Gagal memuat"), bagian lain tetap tampil.
class RingkasanDashboard111
{
    // "Hari ini" mengikuti tanggal di Indonesia, karena tanggal aktivitas diisi pengguna sesuai
    // tanggal setempat. Zona waktu aplikasi (config/app.php) masih UTC, sehingga pukul 00.00-07.00
    // WIB tanpa ini akan terbaca sebagai hari kemarin.
    private const ZONA_WAKTU = 'Asia/Jakarta';

    /** @var array<string, true> */
    private array $tabel = [];

    public function untuk(User $pengguna, ?Carbon $hariIni = null): array
    {
        $hariIni ??= Carbon::now(self::ZONA_WAKTU)->startOfDay();
        // Satu kueri untuk mengetahui tabel mana yang sudah ada, bukan satu kueri per tabel.
        $this->tabel = array_fill_keys(Schema::getTableListing(schemaQualified: false), true);

        $emisi = $this->bagian(fn () => $this->emisi($pengguna, $hariIni));

        return [
            'emisi' => $emisi,
            'target' => $this->bagian(fn () => $this->target($pengguna, $hariIni)),
            'poin' => $this->bagian(fn () => $this->poin($pengguna)),
            'terbaru' => $this->bagian(fn () => $this->aktivitasTerbaru($pengguna)),
            'pengumuman' => $this->bagian(fn () => $this->pengumuman($hariIni)),
            'adaCatatan' => ($emisi['jumlahCatatan'] ?? 0) > 0,
        ];
    }

    // Emisi hari ini dan bulan ini dalam satu kueri.
    private function emisi(User $pengguna, Carbon $hariIni): array
    {
        if (! $this->ada('activities')) {
            return ['hariIni' => 0.0, 'bulanIni' => 0.0, 'jumlahCatatan' => 0];
        }

        $baris = DB::table('activities')
            ->where('user_id', $pengguna->id)
            ->selectRaw('COUNT(*) as jumlah')
            ->selectRaw('COALESCE(SUM(CASE WHEN activity_date = ? THEN emission ELSE 0 END), 0) as hari_ini', [$hariIni->toDateString()])
            ->selectRaw('COALESCE(SUM(CASE WHEN activity_date BETWEEN ? AND ? THEN emission ELSE 0 END), 0) as bulan_ini', [
                $hariIni->copy()->startOfMonth()->toDateString(),
                $hariIni->copy()->endOfMonth()->toDateString(),
            ])
            ->first();

        return [
            'hariIni' => (float) $baris->hari_ini,
            'bulanIni' => (float) $baris->bulan_ini,
            'jumlahCatatan' => (int) $baris->jumlah,
        ];
    }

    // Sisa kuota target yang sedang berjalan. Tanpa target berjalan: null, supaya kartunya
    // menampilkan tombol "Tetapkan Target", bukan angka nol yang membingungkan.
    private function target(User $pengguna, Carbon $hariIni): ?array
    {
        if (! $this->ada('targets')) {
            return null;
        }

        $tanggal = $hariIni->toDateString();
        $target = DB::table('targets')
            ->where('user_id', $pengguna->id)
            ->where('period_start', '<=', $tanggal)
            ->where('period_end', '>=', $tanggal)
            ->orderByDesc('period_start')
            ->first();

        if (! $target) {
            return null;
        }

        $terpakai = $this->ada('activities')
            ? (float) DB::table('activities')
                ->where('user_id', $pengguna->id)
                ->whereBetween('activity_date', [$target->period_start, $target->period_end])
                ->sum('emission')
            : 0.0;

        return [
            'batas' => (float) $target->emission_limit,
            'terpakai' => $terpakai,
            'sisa' => (float) $target->emission_limit - $terpakai,
        ];
    }

    // EcoPoint: kolom users.points bila sudah ada (modul Gamifikasi yang mengisinya),
    // kalau belum, jumlah dari point_logs, kalau belum juga, nol.
    private function poin(User $pengguna): int
    {
        if (array_key_exists('points', $pengguna->getAttributes())) {
            return (int) $pengguna->getAttribute('points');
        }

        return $this->ada('point_logs')
            ? (int) DB::table('point_logs')->where('user_id', $pengguna->id)->sum('points')
            : 0;
    }

    private function aktivitasTerbaru(User $pengguna): Collection
    {
        if (! $this->ada('activities')) {
            return collect();
        }

        $kueri = DB::table('activities as a')
            ->where('a.user_id', $pengguna->id)
            ->orderByDesc('a.activity_date')
            ->orderByDesc('a.id')
            ->limit(5)
            ->select('a.id', 'a.amount', 'a.emission', 'a.activity_date', 'a.note');

        // Nama aktivitas dan kategorinya diambil sekalian lewat join, bukan satu kueri per baris.
        if ($this->ada('emission_factors')) {
            $kueri->leftJoin('emission_factors as f', 'f.id', '=', 'a.emission_factor_id')
                ->addSelect('f.name as nama', 'f.unit as satuan');

            if ($this->ada('categories')) {
                $kueri->leftJoin('categories as k', 'k.id', '=', 'f.category_id')->addSelect('k.name as kategori');
            }
        }

        return $kueri->get();
    }

    private function pengumuman(Carbon $hariIni): ?object
    {
        if (! $this->ada('announcements')) {
            return null;
        }

        $tanggal = $hariIni->toDateString();

        return DB::table('announcements')
            ->where('start_date', '<=', $tanggal)
            ->where('end_date', '>=', $tanggal)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first(['title', 'content']);
    }

    private function ada(string $tabel): bool
    {
        return isset($this->tabel[$tabel]);
    }

    // Galat satu bagian dicatat di log dan hanya membuat bagian itu null.
    private function bagian(callable $hitung): mixed
    {
        try {
            return $hitung();
        } catch (Throwable $e) {
            Log::warning('Dashboard: satu bagian gagal dimuat', ['galat' => $e->getMessage()]);

            return ['gagal' => true];
        }
    }
}
