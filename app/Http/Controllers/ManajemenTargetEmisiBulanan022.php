<?php

namespace App\Http\Controllers;

use App\Events\PemakaianTargetBerubah022;
use App\Http\Requests\SimpanManajemenTargetEmisiBulanan022;
use App\Models\Target022;
use App\Models\User;
use App\Rules\PeriodeTidakTumpangTindih022;
use App\Services\RingkasanTarget022;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// PBI-10 Manajemen Target Emisi Bulanan (KF-11).
class ManajemenTargetEmisiBulanan022 extends Controller
{
    public function __construct(private readonly RingkasanTarget022 $ringkasan) {}

    public function index(Request $request): View
    {
        $pengguna = $request->user();
        $hariIni = RingkasanTarget022::hariIni()->toDateString();
        $semua = Target022::where('user_id', $pengguna->id)->orderByDesc('period_start')->get();

        // Target yang periodenya sudah lewat otomatis pindah ke riwayat, tanpa disimpan statusnya.
        $berjalan = $semua->first(fn ($t) => $t->period_start <= $hariIni && $t->period_end >= $hariIni);
        $akanDatang = $semua->filter(fn ($t) => $t->period_start > $hariIni)->sortBy('period_start')->values();
        $riwayat = $semua->filter(fn ($t) => $t->period_end < $hariIni)->values();

        // Seluruh target diringkas dengan satu kueri ke tabel activities, berapa pun jumlahnya.
        $ringkas = $this->ringkasan->ringkasBanyak($semua);

        return view('target-pengingat-dan-laporan.manajemen-target-emisi-bulanan-022.index', [
            'berjalan' => $berjalan,
            'ringkasanBerjalan' => $berjalan ? $ringkas[$berjalan->id] : null,
            'akanDatang' => $akanDatang->map(fn ($t) => [$t, $ringkas[$t->id]]),
            'riwayat' => $riwayat->map(fn ($t) => [$t, $ringkas[$t->id]]),
            'saran' => $berjalan ? null : $this->ringkasan->saranBatas($pengguna),
        ]);
    }

    public function store(SimpanManajemenTargetEmisiBulanan022 $request): RedirectResponse
    {
        $target = $this->simpanTanpaBentrok($request, fn (array $data) => Target022::create($data + ['user_id' => $request->user()->id]));

        if (! $target) {
            return back()->withInput()->withErrors(['period_start' => PeriodeTidakTumpangTindih022::PESAN]);
        }

        PemakaianTargetBerubah022::dispatch($target, $this->ringkasan->ringkas($target));

        return redirect()->route('target.index')->with('sukses', 'Target ditetapkan.');
    }

    // Validasi sudah menolak periode bertabrakan, tetapi dua kiriman di detik yang sama bisa sama-sama
    // lolos. Karena itu penyimpanan diulang-periksa di dalam transaksi yang mengunci baris pengguna,
    // sehingga kiriman kedua menunggu yang pertama selesai. Null berarti periode ternyata bertabrakan.
    private function simpanTanpaBentrok(SimpanManajemenTargetEmisiBulanan022 $request, callable $simpan, ?Target022 $kecuali = null): ?Target022
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data, $simpan, $kecuali) {
            User::whereKey($request->user()->id)->lockForUpdate()->first();

            if (PeriodeTidakTumpangTindih022::bentrok($request->user()->id, $data['period_start'], $data['period_end'], $kecuali?->id)) {
                return null;
            }

            return $simpan($data);
        });
    }

    public function update(SimpanManajemenTargetEmisiBulanan022 $request, Target022 $target): RedirectResponse
    {
        Gate::authorize('update', $target);

        // Target yang periodenya sudah berakhir hanya menjadi riwayat dan tidak bisa diubah lagi.
        if ($target->period_end < RingkasanTarget022::hariIni()->toDateString()) {
            return redirect()->route('target.index')->with('galat', 'Target yang periodenya sudah berakhir tidak bisa diubah.');
        }

        $tersimpan = $this->simpanTanpaBentrok($request, fn (array $data) => tap($target)->update($data), $target);

        if (! $tersimpan) {
            return back()->withInput()->withErrors(['period_start' => PeriodeTidakTumpangTindih022::PESAN]);
        }

        PemakaianTargetBerubah022::dispatch($target, $this->ringkasan->ringkas($target));

        return redirect()->route('target.index')->with('sukses', 'Target diperbarui.');
    }

    public function destroy(Request $request, Target022 $target): RedirectResponse
    {
        Gate::authorize('delete', $target);
        $target->delete();

        return redirect()->route('target.index')->with('sukses', 'Target dibatalkan.');
    }

    // GET /api/target/berjalan: sisa kuota periode berjalan, untuk kartu target dan dashboard.
    public function berjalan(Request $request): JsonResponse
    {
        $target = $this->ringkasan->berjalan($request->user());

        if (! $target) {
            return response()->json(['ada_target' => false]);
        }

        return response()->json([
            'ada_target' => true,
            'periode' => ['mulai' => $target->period_start, 'selesai' => $target->period_end],
        ] + $this->ringkasan->ringkas($target));
    }
}
