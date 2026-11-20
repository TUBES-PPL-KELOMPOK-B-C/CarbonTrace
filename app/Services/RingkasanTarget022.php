<?php

namespace App\Services;

use App\Events\PemakaianTargetBerubah022;
use App\Models\Target022;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// PBI-10: menghitung pemakaian target. Sisa kuota tidak pernah disimpan sebagai kolom; selalu
// dihitung dari jumlah emisi tabel activities (milik Modul 2) pada rentang periode targetnya.
class RingkasanTarget022
{
    // Seluruh tanggal dibandingkan dalam waktu Indonesia, karena tanggal aktivitas diisi pengguna
    // sesuai tanggal setempat, sementara zona waktu aplikasi masih UTC.
    public const ZONA_WAKTU = 'Asia/Jakarta';

    private ?bool $adaAktivitas = null;

    public static function hariIni(): Carbon
    {
        return Carbon::now(self::ZONA_WAKTU)->startOfDay();
    }

    public function berjalan(User $pengguna): ?Target022
    {
        $tanggal = self::hariIni()->toDateString();

        return Target022::where('user_id', $pengguna->id)
            ->where('period_start', '<=', $tanggal)
            ->where('period_end', '>=', $tanggal)
            ->first();
    }

    /** @return array{batas: float, terpakai: float, sisa: float, persen: float, status: string, sisa_hari: int} */
    public function ringkas(Target022 $target, ?float $terpakai = null): array
    {
        $batas = (float) $target->emission_limit;
        $terpakai = round($terpakai ?? $this->terpakai($target), 2);
        // Tanpa catatan sama sekali persennya nol, bukan galat pembagian dengan nol.
        $persen = $batas > 0 ? round($terpakai / $batas * 100, 1) : 0.0;

        $hariIni = self::hariIni();
        $selesai = Carbon::parse($target->period_end, self::ZONA_WAKTU)->startOfDay();
        $mulai = Carbon::parse($target->period_start, self::ZONA_WAKTU)->startOfDay();
        $sisaHari = match (true) {
            $hariIni->gt($selesai) => 0,
            $hariIni->lt($mulai) => (int) $mulai->diffInDays($selesai) + 1,
            default => (int) $hariIni->diffInDays($selesai) + 1,
        };

        return [
            'batas' => round($batas, 2),
            'terpakai' => $terpakai,
            'sisa' => round($batas - $terpakai, 2),
            'persen' => $persen,
            'status' => $persen > 100 ? 'terlampaui' : ($persen >= 70 ? 'hampir' : 'aman'),
            'sisa_hari' => $sisaHari,
        ];
    }

    // Saran batas untuk pengguna yang belum punya target: 10 persen di bawah emisinya selama
    // tiga puluh hari terakhir. Null bila belum ada catatan.
    public function saranBatas(User $pengguna): ?float
    {
        if (! $this->adaTabelAktivitas()) {
            return null;
        }

        $hariIni = self::hariIni();
        $total = (float) DB::table('activities')
            ->where('user_id', $pengguna->id)
            ->whereBetween('activity_date', [$hariIni->copy()->subDays(29)->toDateString(), $hariIni->toDateString()])
            ->sum('emission');

        return $total > 0 ? max(0.1, round($total * 0.9, 1)) : null;
    }

    // Dipanggil setiap kali aktivitas pada tanggal tertentu berubah: kabari seluruh target pengguna
    // yang periodenya mencakup tanggal-tanggal itu.
    public function kabariPerubahan(int $penggunaId, array $tanggal): void
    {
        $tanggal = array_values(array_unique(array_filter($tanggal)));

        foreach ($tanggal as $hari) {
            Target022::where('user_id', $penggunaId)
                ->where('period_start', '<=', $hari)
                ->where('period_end', '>=', $hari)
                ->get()
                ->each(fn (Target022 $target) => PemakaianTargetBerubah022::dispatch($target, $this->ringkas($target)));
        }
    }

    // Ringkasan banyak target milik satu pengguna dengan satu kueri: emisi per tanggal diambil
    // sekali untuk rentang gabungan, lalu dijumlahkan per target di PHP.
    /** @return array<int, array{batas: float, terpakai: float, sisa: float, persen: float, status: string, sisa_hari: int}> kunci: id target */
    public function ringkasBanyak(Collection $targets): array
    {
        if ($targets->isEmpty()) {
            return [];
        }

        $perTanggal = [];

        if ($this->adaTabelAktivitas()) {
            $perTanggal = DB::table('activities')
                ->where('user_id', $targets->first()->user_id)
                ->whereBetween('activity_date', [$targets->min('period_start'), $targets->max('period_end').' 23:59:59'])
                ->groupBy('activity_date')
                ->selectRaw('activity_date, SUM(emission) as total')
                ->get()
                // Kolom bisa tersimpan "Y-m-d" atau "Y-m-d 00:00:00"; disamakan jadi "Y-m-d".
                ->reduce(function (array $hasil, $baris) {
                    $tanggal = substr((string) $baris->activity_date, 0, 10);
                    $hasil[$tanggal] = ($hasil[$tanggal] ?? 0) + (float) $baris->total;

                    return $hasil;
                }, []);
        }

        return $targets->mapWithKeys(function (Target022 $target) use ($perTanggal) {
            $terpakai = 0.0;
            foreach ($perTanggal as $tanggal => $total) {
                if ($tanggal >= $target->period_start && $tanggal <= $target->period_end) {
                    $terpakai += $total;
                }
            }

            return [$target->id => $this->ringkas($target, $terpakai)];
        })->all();
    }

    private function terpakai(Target022 $target): float
    {
        if (! $this->adaTabelAktivitas()) {
            return 0.0;
        }

        // Batas akhir sampai pukul 23.59.59 supaya catatan di hari terakhir periode ikut terhitung
        // walau kolom tanggalnya tersimpan beserta jam.
        return (float) DB::table('activities')
            ->where('user_id', $target->user_id)
            ->whereBetween('activity_date', [$target->period_start, $target->period_end.' 23:59:59'])
            ->sum('emission');
    }

    private function adaTabelAktivitas(): bool
    {
        return $this->adaAktivitas ??= Schema::hasTable('activities');
    }
}
