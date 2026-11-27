<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanManajemenLencana015;
use App\Models\Badge015;
use App\Services\PemeriksaSyaratLencana015;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// PBI-13 Manajemen Lencana (KF-14) - Modul Gamifikasi (Man-015).
class ManajemenLencana015 extends Controller
{
    public function __construct(private readonly PemeriksaSyaratLencana015 $pemeriksa) {}

    // GET /admin/lencana: kisi kartu lencana bagi admin, beserta jumlah peraih tiap lencana.
    public function index(): View
    {
        Gate::authorize('viewAny', Badge015::class);

        $lencana = Badge015::query()
            ->withCount(['pengguna as jumlah_peraih'])
            ->orderBy('type')
            ->orderBy('required_value')
            ->get();

        return view('gamifikasi.manajemen-lencana-015.index', [
            'daftar' => $lencana,
            'jenis' => Badge015::JENIS,
        ]);
    }

    public function store(SimpanManajemenLencana015 $request): RedirectResponse
    {
        Gate::authorize('create', Badge015::class);

        Badge015::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('lencana.index')->with('sukses', 'Lencana ditambahkan.');
    }

    public function update(SimpanManajemenLencana015 $request, Badge015 $lencana): RedirectResponse
    {
        Gate::authorize('update', $lencana);

        // Syarat boleh diturunkan maupun dinaikkan. Diturunkan: pengguna yang tadinya belum
        // memenuhi langsung meraihnya pada pemeriksaan berikutnya, dan itu memang benar.
        // Dinaikkan: baris badge_user yang sudah ada tidak pernah dihapus, jadi lencana yang
        // pernah diraih tidak dicabut.
        $lencana->update($request->validated());

        return redirect()->route('lencana.index')->with('sukses', 'Syarat lencana diperbarui.');
    }

    // PATCH /admin/lencana/{lencana}/status: menonaktifkan tanpa menghapus. Lencana nonaktif
    // hilang dari galeri publik tetapi tetap terlihat pada pengguna yang sudah meraihnya.
    public function status(Badge015 $lencana): RedirectResponse
    {
        Gate::authorize('update', $lencana);

        $lencana->update(['is_active' => ! $lencana->is_active]);

        return redirect()->route('lencana.index')
            ->with('sukses', $lencana->is_active ? 'Lencana diaktifkan.' : 'Lencana dinonaktifkan.');
    }

    public function destroy(Badge015 $lencana): RedirectResponse
    {
        Gate::authorize('delete', $lencana);

        // Lencana yang pernah diraih tidak boleh hilang dari galeri orang yang sudah meraihnya,
        // jadi penghapusan permanen hanya untuk lencana yang belum diraih siapa pun.
        $peraih = $this->pemeriksa->jumlahPeraih($lencana);

        if ($peraih > 0) {
            return redirect()->route('lencana.index')
                ->with('galat', "Lencana ini sudah diraih {$peraih} pengguna. Nonaktifkan saja.");
        }

        $lencana->delete();

        return redirect()->route('lencana.index')->with('sukses', 'Lencana dihapus.');
    }

    // GET /lencana: galeri lencana pengguna. Pemeriksaan syarat dijalankan sekali di sini supaya
    // lencana yang baru layak langsung muncul, lalu dirayakan satu kali lewat modal.
    public function galeri(Request $request): View
    {
        $pengguna = $request->user();

        $baru = DB::transaction(fn () => $this->pemeriksa->periksa($pengguna));

        return view('gamifikasi.manajemen-lencana-015.galeri', [
            'daftar' => $this->pemeriksa->galeri($pengguna),
            'baruDiraih' => $baru,
        ]);
    }
}
