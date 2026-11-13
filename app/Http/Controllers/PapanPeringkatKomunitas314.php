<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanPapanPeringkatKomunitas314;
use App\Models\User314;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PapanPeringkatKomunitas314 extends Controller
{
    /**
     * Menampilkan halaman utama papan peringkat komunitas.
     */
    public function index(SimpanPapanPeringkatKomunitas314 $request): View
    {
        Gate::authorize('lihat-papan-peringkat');

        $periode = $request->input('periode', 'bulan') ?: 'bulan';
        $urut = $request->input('urut', 'poin') ?: 'poin';
        $halaman = (int) ($request->input('halaman', 1) ?: 1);

        $hasil = $this->ambilDataPeringkat($periode, $urut);
        $totalPeserta = count($hasil);

        // Cari posisi pengguna yang sedang login
        $currentUser = auth()->user();
        $posisiSaya = $this->temukanPosisiSaya($hasil, $currentUser, $totalPeserta);

        // Ambil 3 besar untuk podium
        $podium = array_slice($hasil, 0, 3);

        // Paginasi manual untuk tabel (misal 10 per halaman mulai dari peringkat 4 atau semua)
        $perHalaman = 10;
        $offset = ($halaman - 1) * $perHalaman;
        $dataTabel = array_slice($hasil, $offset, $perHalaman);
        $totalHalaman = max(1, (int) ceil($totalPeserta / $perHalaman));

        return view('akun-dan-administrasi-pengguna.papan-peringkat-komunitas-314.index', [
            'periode' => $periode,
            'urut' => $urut,
            'halaman' => $halaman,
            'totalHalaman' => $totalHalaman,
            'podium' => $podium,
            'dataTabel' => $dataTabel,
            'saya' => $posisiSaya,
            'totalPeserta' => $totalPeserta,
            'semuaData' => $hasil,
        ]);
    }

    /**
     * Endpoint API JSON untuk papan peringkat (AJAX / pergantian tab).
     */
    public function apiData(SimpanPapanPeringkatKomunitas314 $request): JsonResponse
    {
        Gate::authorize('lihat-papan-peringkat');

        $periode = $request->input('periode', 'bulan') ?: 'bulan';
        $urut = $request->input('urut', 'poin') ?: 'poin';
        $halaman = (int) ($request->input('halaman', 1) ?: 1);

        $hasil = $this->ambilDataPeringkat($periode, $urut);
        $totalPeserta = count($hasil);

        $currentUser = auth()->user();
        $posisiSaya = $this->temukanPosisiSaya($hasil, $currentUser, $totalPeserta);

        // Paginasi jika diminta, default ambil 20
        $perHalaman = 20;
        $offset = ($halaman - 1) * $perHalaman;
        $dataSlice = array_slice($hasil, $offset, $perHalaman);

        return response()->json([
            'periode' => $periode,
            'urut' => $urut,
            'data' => $dataSlice,
            'saya' => $posisiSaya,
            'total_peserta' => $totalPeserta,
        ]);
    }

    /**
     * Mengambil data peringkat dengan agregasi database groupBy dan cache 5 menit.
     */
    protected function ambilDataPeringkat(string $periode, string $urut): array
    {
        $cacheKey = "papan_peringkat_{$periode}_{$urut}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($periode, $urut) {
            // Tentukan rentang waktu untuk point_logs
            $startDate = match ($periode) {
                'minggu' => now()->subDays(7)->toDateTimeString(),
                'bulan' => now()->subDays(30)->toDateTimeString(),
                default => null,
            };

            // Query agregasi groupBy di sisi basis data
            $query = DB::table('users')
                ->where('users.is_active', true)
                ->select([
                    'users.id',
                    'users.name',
                    'users.photo',
                    'users.points as user_points',
                    'users.streak_count',
                    'users.created_at',
                ]);

            if ($startDate) {
                $query->leftJoin('point_logs', function ($join) use ($startDate) {
                    $join->on('point_logs.user_id', '=', 'users.id')
                        ->where('point_logs.created_at', '>=', $startDate);
                });
                $query->addSelect(DB::raw('COALESCE(SUM(point_logs.points), 0) as aggregate_points'));
            } else {
                $query->leftJoin('point_logs', 'point_logs.user_id', '=', 'users.id');
                $query->addSelect(DB::raw('CASE WHEN COALESCE(SUM(point_logs.points), 0) > 0 THEN SUM(point_logs.points) ELSE users.points END as aggregate_points'));
            }

            // Agregasi progress penurunan dari challenge_user jika ada
            $query->leftJoin('challenge_user', 'challenge_user.user_id', '=', 'users.id');
            $query->addSelect(DB::raw('ROUND(COALESCE(AVG(challenge_user.progress), 0), 1) as aggregate_penurunan'));

            $query->groupBy(
                'users.id',
                'users.name',
                'users.photo',
                'users.points',
                'users.streak_count',
                'users.created_at'
            );

            // Pengurutan sesuai kriteria dan aturan tie-breaker
            match ($urut) {
                'streak' => $query->orderByDesc('users.streak_count')
                    ->orderByDesc('aggregate_points')
                    ->orderBy('users.created_at', 'asc')
                    ->orderBy('users.id', 'asc'),
                'penurunan' => $query->orderByDesc('aggregate_penurunan')
                    ->orderByDesc('aggregate_points')
                    ->orderByDesc('users.streak_count')
                    ->orderBy('users.created_at', 'asc')
                    ->orderBy('users.id', 'asc'),
                default => $query->orderByDesc('aggregate_points')
                    ->orderByDesc('users.streak_count')
                    ->orderBy('users.created_at', 'asc')
                    ->orderBy('users.id', 'asc'),
            };

            $records = $query->get();

            $list = [];
            $rank = 1;
            foreach ($records as $item) {
                $list[] = [
                    'peringkat' => $rank++,
                    'user_id' => (int) $item->id,
                    'nama' => $item->name,
                    'foto' => $item->photo ? (str_starts_with($item->photo, 'http') ? $item->photo : asset('storage/' . $item->photo)) : null,
                    'poin' => (int) $item->aggregate_points,
                    'streak' => (int) $item->streak_count,
                    'penurunan' => (float) $item->aggregate_penurunan,
                ];
            }

            return $list;
        });
    }

    /**
     * Menentukan posisi profil pengguna yang sedang login.
     */
    protected function temukanPosisiSaya(array $rankedList, ?User314 $currentUser, int $totalPeserta): array
    {
        if (!$currentUser) {
            return [
                'peringkat' => $totalPeserta > 0 ? $totalPeserta : 1,
                'poin' => 0,
                'streak' => 0,
                'penurunan' => 0.0,
            ];
        }

        foreach ($rankedList as $row) {
            if ($row['user_id'] === (int) $currentUser->id) {
                return [
                    'peringkat' => $row['peringkat'],
                    'poin' => $row['poin'],
                    'streak' => $row['streak'],
                    'penurunan' => $row['penurunan'],
                ];
            }
        }

        // Jika pengguna belum punya catatan poin / posisi di luar daftar
        return [
            'peringkat' => max(1, $totalPeserta),
            'poin' => (int) $currentUser->points,
            'streak' => (int) $currentUser->streak_count,
            'penurunan' => 0.0,
        ];
    }
}
