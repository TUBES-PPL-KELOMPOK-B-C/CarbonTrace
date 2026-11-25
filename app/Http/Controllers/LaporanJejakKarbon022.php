<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanLaporanJejakKarbon022;
use App\Models\SavedReport022;
use App\Services\RingkasanLaporan022;
use App\Services\RingkasanTarget022;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// PBI-12 Laporan Jejak Karbon (KF-13).
// TODO: laporan.unduh (PDF) menyusul setelah paket barryvdh/laravel-dompdf terpasang.
class LaporanJejakKarbon022 extends Controller
{
    private const VIEW = 'target-pengingat-dan-laporan.laporan-jejak-karbon-022.';

    public function __construct(private readonly RingkasanLaporan022 $ringkasan) {}

    public function index(Request $request): View
    {
        return view(self::VIEW.'index', [
            'laporan' => SavedReport022::where('user_id', $request->user()->id)->latest('id')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view(self::VIEW.'susun', $this->bahanFormulir($request));
    }

    public function store(SimpanLaporanJejakKarbon022 $request): RedirectResponse
    {
        $laporan = SavedReport022::create($request->kolom() + ['user_id' => $request->user()->id]);

        return redirect()->route('laporan.show', $laporan)->with('sukses', 'Laporan tersimpan.');
    }

    public function show(Request $request, SavedReport022 $laporan): View
    {
        Gate::authorize('view', $laporan);

        return view(self::VIEW.'show', $this->bahanFormulir($request) + [
            'laporan' => $laporan,
            'angka' => $this->ringkasan->untuk($laporan->user_id, $laporan->period_start, $laporan->period_end, $laporan->kategoriIds()),
        ]);
    }

    public function update(SimpanLaporanJejakKarbon022 $request, SavedReport022 $laporan): RedirectResponse
    {
        Gate::authorize('update', $laporan);
        $laporan->update($request->kolom());

        return redirect()->route('laporan.show', $laporan)->with('sukses', 'Laporan diperbarui.');
    }

    public function destroy(SavedReport022 $laporan): RedirectResponse
    {
        Gate::authorize('delete', $laporan);
        $laporan->delete();

        return redirect()->route('laporan.index')->with('sukses', 'Laporan dihapus.');
    }

    // POST /api/laporan/pratinjau: angka ringkas tanpa menyimpan baris saved_reports.
    public function pratinjau(SimpanLaporanJejakKarbon022 $request): JsonResponse
    {
        return response()->json($this->ringkasan->untuk(
            $request->user()->id,
            $request->validated('period_start'),
            $request->validated('period_end'),
            $request->kategoriIds(),
        ));
    }

    // Kategori dan pintasan rentang tanggal; seluruh pintasan berakhir paling lambat hari ini.
    private function bahanFormulir(Request $request): array
    {
        $hariIni = RingkasanTarget022::hariIni();
        $sampai = $hariIni->toDateString();
        $pintasan = [
            '7 hari' => [$hariIni->copy()->subDays(6)->toDateString(), $sampai],
            '30 hari' => [$hariIni->copy()->subDays(29)->toDateString(), $sampai],
            'Bulan ini' => [$hariIni->copy()->startOfMonth()->toDateString(), $sampai],
        ];

        if ($target = app(RingkasanTarget022::class)->berjalan($request->user())) {
            $pintasan['Periode target'] = [$target->period_start, min($target->period_end, $sampai)];
        }

        return ['kategori' => RingkasanLaporan022::kategori(), 'pintasan' => $pintasan, 'hariIni' => $sampai];
    }
}
