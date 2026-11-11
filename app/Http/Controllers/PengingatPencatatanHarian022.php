<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanPengingatPencatatanHarian022;
use App\Models\Reminder022;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// PBI-11 Pengingat Pencatatan Harian (KF-12). Pengirimannya ada di perintah pengingat:kirim.
class PengingatPencatatanHarian022 extends Controller
{
    public function index(Request $request): View
    {
        $pengingat = Reminder022::where('user_id', $request->user()->id)->orderBy('remind_time')->get();

        return view('target-pengingat-dan-laporan.pengingat-pencatatan-harian-022.index', [
            'pengingat' => $pengingat,
            'bisaTambah' => $pengingat->where('is_active', true)->count() < SimpanPengingatPencatatanHarian022::MAKS_AKTIF,
        ]);
    }

    public function store(SimpanPengingatPencatatanHarian022 $request): RedirectResponse
    {
        $pengingat = Reminder022::create([
            'user_id' => $request->user()->id,
            'remind_time' => $request->validated('remind_time'),
            'is_active' => true,
        ]);

        return redirect()->route('pengingat.index')->with('sukses', 'Pengingat disetel pukul '.$this->jamTeks($pengingat).'.');
    }

    public function update(SimpanPengingatPencatatanHarian022 $request, Reminder022 $pengingat): RedirectResponse
    {
        Gate::authorize('update', $pengingat);
        $pengingat->update(['remind_time' => $request->validated('remind_time')]);

        return redirect()->route('pengingat.index')->with('sukses', 'Pengingat disetel pukul '.$this->jamTeks($pengingat).'.');
    }

    // Menyalakan atau mematikan. Menyalakan tetap tunduk pada batas tiga pengingat aktif.
    public function saklar(Request $request, Reminder022 $pengingat): RedirectResponse
    {
        Gate::authorize('update', $pengingat);
        $nyala = $request->boolean('is_active');

        if ($nyala && ! $pengingat->is_active
            && SimpanPengingatPencatatanHarian022::jumlahAktif($pengingat->user_id) >= SimpanPengingatPencatatanHarian022::MAKS_AKTIF) {
            return redirect()->route('pengingat.index')->with('galat', 'Maksimal tiga pengingat aktif.');
        }

        $pengingat->update(['is_active' => $nyala]);

        return redirect()->route('pengingat.index')->with('sukses', $nyala
            ? 'Pengingat pukul '.$this->jamTeks($pengingat).' dinyalakan.'
            : 'Pengingat pukul '.$this->jamTeks($pengingat).' dimatikan.');
    }

    public function destroy(Request $request, Reminder022 $pengingat): RedirectResponse
    {
        Gate::authorize('delete', $pengingat);
        $pengingat->delete();

        return redirect()->route('pengingat.index')->with('sukses', 'Pengingat dihapus.');
    }

    // "20:00" ditulis "20.00" sesuai kebiasaan penulisan jam di Indonesia.
    private function jamTeks(Reminder022 $pengingat): string
    {
        return str_replace(':', '.', $pengingat->jam());
    }
}
