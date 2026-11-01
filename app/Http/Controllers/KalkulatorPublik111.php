<?php

namespace App\Http\Controllers;

use App\Http\Requests\HitungKalkulator111;
use App\Services\PerhitunganEmisi111;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

// KF-24 Kalkulator Jejak Karbon Publik: dipakai tanpa akun dan tidak menulis ke basis data.
class KalkulatorPublik111 extends Controller
{
    // Pembanding untuk kalimat "Setara ... km berkendara motor", dalam kg CO2e per km.
    private const KG_PER_KM_MOTOR = 0.12;

    public function __construct(private readonly PerhitunganEmisi111 $perhitungan) {}

    public function index(): View
    {
        $kategori = self::faktorAktif()
            ->groupBy('category_id')
            ->map(fn (Collection $faktor) => (object) [
                'id' => $faktor->first()->category_id,
                'nama' => $faktor->first()->category_name,
                'faktor' => $faktor,
            ])
            ->values();

        return view('publik.kalkulator-111', [
            'kategori' => $kategori,
            'hasil' => session('hasil_kalkulator'),
        ]);
    }

    // Versi tanpa JavaScript: formulir dikirim biasa, hasilnya muncul setelah halaman dimuat ulang.
    public function hitung(HitungKalkulator111 $request): RedirectResponse
    {
        return redirect()
            ->to(route('kalkulator.index').'#hasil')
            ->withInput()
            ->with('hasil_kalkulator', $this->jalankan($request));
    }

    public function api(HitungKalkulator111 $request): JsonResponse
    {
        return response()->json($this->jalankan($request));
    }

    // Faktor emisi aktif beserta kategorinya. Tabelnya milik PBI Renaya (154); selama belum ada
    // atau masih kosong, halaman menampilkan keadaan "kalkulator belum siap".
    public static function faktorAktif(): Collection
    {
        if (! Schema::hasTable('emission_factors') || ! Schema::hasTable('categories')) {
            return collect();
        }

        return DB::table('emission_factors as f')
            ->join('categories as k', 'k.id', '=', 'f.category_id')
            ->where('f.is_active', true)
            ->where('k.is_active', true)
            ->orderBy('k.id')
            ->orderBy('f.name')
            ->get(['f.id', 'f.name', 'f.unit', 'f.factor_value', 'f.is_active', 'f.category_id', 'k.name as category_name']);
    }

    private function jalankan(HitungKalkulator111 $request): array
    {
        $faktor = $request->faktor();
        $jumlah = (float) $request->validated('amount');

        // Rumus dari KF-20, sama dengan yang dipakai pencatatan aktivitas; tanpa pengguna, jadi
        // listrik tidak dibagi jumlah penghuni rumah.
        $hasil = $this->perhitungan->hitung($faktor, $jumlah);

        $data = $hasil->toArray() + [
            'perbandingan' => $this->perbandingan((float) $hasil->emisi),
            'faktor' => ['id' => $faktor->id, 'nama' => $faktor->name, 'kategori' => $faktor->category_name, 'satuan' => $faktor->unit],
            'jumlah' => $jumlah,
        ];

        // Hasil hanya hidup di sesi, tidak ada baris yang ditulis ke basis data.
        // TODO (PBI-04, Hadi): tawarkan hasil ini sebagai catatan pertama setelah pengunjung mendaftar
        session(['kalkulator_terakhir' => $data]);

        return $data;
    }

    private function perbandingan(float $emisi): string
    {
        if ($emisi <= 0) {
            return 'Hampir tidak ada emisi. Mantap!';
        }

        return 'Setara '.PerhitunganEmisi111::angka($emisi / self::KG_PER_KM_MOTOR, 1).' km berkendara motor';
    }
}
