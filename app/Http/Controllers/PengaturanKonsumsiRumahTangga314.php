<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanPengaturanKonsumsiRumahTangga314;
use App\Models\UserSetting314;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PengaturanKonsumsiRumahTangga314 extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $setting = $user->setting;

        if ($setting) {
            Gate::authorize('view', $setting);
        }

        return view('akun-dan-administrasi-pengguna.pengaturan-konsumsi-rumah-tangga-314.index', [
            'user' => $user,
            'setting' => $setting,
        ]);
    }

    public function upsert(SimpanPengaturanKonsumsiRumahTangga314 $request): RedirectResponse
    {
        $user = $request->user();
        $setting = $user->setting;

        if ($setting) {
            Gate::authorize('update', $setting);
        }

        $validated = $request->validated();

        // Menggunakan transaksi database agar dua request bersamaan tidak menimbulkan baris ganda
        DB::transaction(function () use ($user, $validated) {
            UserSetting314::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'household_size' => $validated['household_size'],
                    'power_watt' => $validated['power_watt'],
                    'vehicle_ownership' => $validated['vehicle_ownership'],
                ]
            );
        });

        return redirect()->route('rumah.index')->with('sukses', 'Pengaturan tersimpan. Berlaku untuk pencatatan berikutnya.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $setting = $user->setting;

        if ($setting) {
            Gate::authorize('delete', $setting);
            $setting->delete();
        }

        return redirect()->route('rumah.index')->with('sukses', 'Data rumah tangga berhasil dihapus. Emisi listrik akan dihitung penuh.');
    }
}
