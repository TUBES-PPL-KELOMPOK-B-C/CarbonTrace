<?php

namespace App\Http\Controllers;

use App\Events\PemakaianTargetBerubah022;
use App\Http\Requests\SimpanManajemenTargetEmisiBulanan022;
use App\Models\Target022;
use App\Services\RingkasanTarget022;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('target-pengingat-dan-laporan.manajemen-target-emisi-bulanan-022.index', [
            'berjalan' => $berjalan,
            'ringkasanBerjalan' => $berjalan ? $this->ringkasan->ringkas($berjalan) : null,
            'akanDatang' => $akanDatang->map(fn ($t) => [$t, $this->ringkasan->ringkas($t)]),
            'riwayat' => $riwayat->map(fn ($t) => [$t, $this->ringkasan->ringkas($t)]),
            'saran' => $berjalan ? null : $this->ringkasan->saranBatas($pengguna),
        ]);
    }

    public function store(SimpanManajemenTargetEmisiBulanan022 $request): RedirectResponse
    {
        $target = Target022::create($request->validated() + ['user_id' => $request->user()->id]);
        PemakaianTargetBerubah022::dispatch($target, $this->ringkasan->ringkas($target));

        return redirect()->route('target.index')->with('sukses', 'Target ditetapkan.');
    }

    public function update(SimpanManajemenTargetEmisiBulanan022 $request, Target022 $target): RedirectResponse
    {
        Gate::authorize('update', $target);

        // Target yang periodenya sudah berakhir hanya menjadi riwayat dan tidak bisa diubah lagi.
        if ($target->period_end < RingkasanTarget022::hariIni()->toDateString()) {
            return redirect()->route('target.index')->with('galat', 'Target yang periodenya sudah berakhir tidak bisa diubah.');
        }

        $target->update($request->validated());
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
