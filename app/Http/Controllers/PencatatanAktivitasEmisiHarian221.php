<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanPencatatanAktivitasEmisiHarian221;
use App\Models\Activitie221;
use App\Models\ActivityTemplate221;
use App\Models\Category;
use App\Models\EmissionFactor;
use App\Services\EmissionCalculatorService221;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PencatatanAktivitasEmisiHarian221 extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected EmissionCalculatorService221 $calculator
    ) {}

    /**
     * Tampilkan riwayat catatan aktivitas emisi harian (Read).
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $query = Activitie221::with(['emissionFactor.category'])
            ->where('user_id', $userId);

        // Filter kategori
        if ($request->filled('kategori_id')) {
            $query->whereHas('emissionFactor', function ($q) use ($request) {
                $q->where('category_id', $request->kategori_id);
            });
        }

        // Filter rentang tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('activity_date', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('activity_date', '<=', $request->tanggal_selesai);
        }

        // Kueri total emisi pada rentang terfilter
        $totalEmisiRentang = (clone $query)->sum('emission');

        // Data riwayat dengan paginasi
        $aktivitas = $query->orderBy('activity_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Data pendukung untuk pemilih formulir & filter
        $kategoriList = Category::where('is_active', true)
            ->with(['emissionFactors' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $faktorEmisiList = EmissionFactor::where('is_active', true)
            ->with('category')
            ->get();

        return view('pencatatan-aktivitas.pencatatan-aktivitas-emisi-harian-221.index', [
            'aktivitas' => $aktivitas,
            'totalEmisiRentang' => $totalEmisiRentang,
            'kategoriList' => $kategoriList,
            'faktorEmisiList' => $faktorEmisiList,
            'bukaFormulir' => $request->get('buka') === 'buat',
        ]);
    }

    /**
     * Tampilkan formulir pencatatan aktivitas.
     */
    public function create(): View
    {
        $kategoriList = Category::where('is_active', true)
            ->with(['emissionFactors' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $faktorEmisiList = EmissionFactor::where('is_active', true)
            ->with('category')
            ->get();

        return view('pencatatan-aktivitas.pencatatan-aktivitas-emisi-harian-221.create', [
            'kategoriList' => $kategoriList,
            'faktorEmisiList' => $faktorEmisiList,
        ]);
    }

    /**
     * Simpan catatan aktivitas baru ke basis data (Create).
     */
    public function store(SimpanPencatatanAktivitasEmisiHarian221 $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Hitung emisi melalui service terpusat
        $hasilHitung = $this->calculator->calculate(
            $validated['emission_factor_id'],
            (float) $validated['amount'],
            $user
        );

        $aktivitas = Activitie221::create([
            'user_id' => $user->id,
            'emission_factor_id' => $validated['emission_factor_id'],
            'amount' => round((float) $validated['amount'], 4),
            'emission' => $hasilHitung['emisi_persis'],
            'activity_date' => $validated['activity_date'],
            'note' => $validated['note'] ?? null,
        ]);

        // Simpan sebagai template jika dicentang oleh pengguna
        if ($request->boolean('simpan_template') && $request->filled('nama_template')) {
            ActivityTemplate221::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $request->nama_template,
                ],
                [
                    'emission_factor_id' => $validated['emission_factor_id'],
                    'amount' => round((float) $validated['amount'], 4),
                    'estimated_emission' => $hasilHitung['emisi_persis'],
                ]
            );
        }

        $emisiFormatted = str_replace('.', ',', (string) $hasilHitung['emisi']);

        return redirect()->route('aktivitas.index')
            ->with('sukses', "Tercatat {$emisiFormatted} kg CO2e.");
    }

    /**
     * Tampilkan panel/halaman ubah catatan (Edit).
     */
    public function edit(Activitie221 $aktivitas): View
    {
        $this->authorize('update', $aktivitas);

        $kategoriList = Category::where('is_active', true)
            ->with(['emissionFactors' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $faktorEmisiList = EmissionFactor::where('is_active', true)
            ->with('category')
            ->get();

        return view('pencatatan-aktivitas.pencatatan-aktivitas-emisi-harian-221.edit', [
            'aktivitas' => $aktivitas,
            'kategoriList' => $kategoriList,
            'faktorEmisiList' => $faktorEmisiList,
        ]);
    }

    /**
     * Perbarui catatan aktivitas yang sudah ada (Update).
     */
    public function update(SimpanPencatatanAktivitasEmisiHarian221 $request, Activitie221 $aktivitas): RedirectResponse
    {
        $this->authorize('update', $aktivitas);

        $validated = $request->validated();
        $user = auth()->user();

        // Hanya hitung ulang jika jumlah atau faktor emisi berubah
        $faktorBerubah = (int) $aktivitas->emission_factor_id !== (int) $validated['emission_factor_id'];
        $jumlahBerubah = abs((float) $aktivitas->amount - (float) $validated['amount']) > 0.00001;

        $nilaiEmisi = $aktivitas->emission;
        if ($faktorBerubah || $jumlahBerubah) {
            $hasilHitung = $this->calculator->calculate(
                $validated['emission_factor_id'],
                (float) $validated['amount'],
                $user
            );
            $nilaiEmisi = $hasilHitung['emisi_persis'];
        }

        $aktivitas->update([
            'emission_factor_id' => $validated['emission_factor_id'],
            'amount' => round((float) $validated['amount'], 4),
            'emission' => $nilaiEmisi,
            'activity_date' => $validated['activity_date'],
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('aktivitas.index')
            ->with('sukses', 'Catatan aktivitas berhasil diperbarui.');
    }

    /**
     * Hapus catatan aktivitas (Delete).
     */
    public function destroy(Activitie221 $aktivitas): RedirectResponse
    {
        $this->authorize('delete', $aktivitas);

        $aktivitas->delete();

        return redirect()->route('aktivitas.index')
            ->with('sukses', 'Catatan aktivitas berhasil dihapus.');
    }

    /**
     * API Pratinjau hitung emisi tanpa menyimpan ke basis data.
     * POST /api/aktivitas/pratinjau
     */
    public function pratinjau(Request $request): JsonResponse
    {
        $request->validate([
            'emission_factor_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $factor = EmissionFactor::with('category')->find($request->emission_factor_id);

        if (!$factor) {
            return response()->json([
                'message' => 'Faktor emisi tidak ditemukan.',
            ], 404);
        }

        if (!$factor->is_active) {
            return response()->json([
                'message' => 'Faktor emisi ini sudah diarsipkan, pilih yang lain.',
            ], 422);
        }

        try {
            $hasil = $this->calculator->calculate($factor, (float) $request->amount, auth()->user());
            return response()->json([
                'emisi' => $hasil['emisi'],
                'satuan' => $hasil['satuan'],
                'rincian' => $hasil['rincian'],
                'faktor' => $hasil['faktor'],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
