<?php

use App\Http\Controllers\ManajemenPenggunaDanPeran314;
use App\Http\Controllers\ManajemenProfilPengguna314;
use App\Http\Controllers\PapanPeringkatKomunitas314;
use App\Http\Controllers\PengaturanKonsumsiRumahTangga314;
use App\Http\Middleware\PastikanAdministrator314;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // PBI-01: Manajemen Profil Pengguna
    Route::get('/profil', [ManajemenProfilPengguna314::class, 'index'])->name('profil.index');
    Route::put('/profil', [ManajemenProfilPengguna314::class, 'update'])->name('profil.update');
    Route::put('/profil/sandi', [ManajemenProfilPengguna314::class, 'sandi'])->name('profil.sandi');
    Route::delete('/profil', [ManajemenProfilPengguna314::class, 'destroy'])->name('profil.destroy');

    // PBI-02: Pengaturan Konsumsi Rumah Tangga
    Route::get('/pengaturan-rumah-tangga', [PengaturanKonsumsiRumahTangga314::class, 'index'])->name('rumah.index');
    Route::put('/pengaturan-rumah-tangga', [PengaturanKonsumsiRumahTangga314::class, 'upsert'])->name('rumah.upsert');
    Route::delete('/pengaturan-rumah-tangga', [PengaturanKonsumsiRumahTangga314::class, 'destroy'])->name('rumah.destroy');

    // PBI-20: Papan Peringkat Komunitas
    Route::get('/papan-peringkat', [PapanPeringkatKomunitas314::class, 'index'])->name('peringkat.index');
    Route::get('/api/papan-peringkat', [PapanPeringkatKomunitas314::class, 'apiData'])->name('peringkat.data');

    // KF-04: Manajemen Pengguna dan Peran (Khusus Administrator)
    Route::middleware(PastikanAdministrator314::class)->prefix('admin')->group(function () {
        Route::get('/pengguna', [ManajemenPenggunaDanPeran314::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna', [ManajemenPenggunaDanPeran314::class, 'store'])->name('pengguna.store');
        Route::patch('/pengguna/{pengguna}/peran', [ManajemenPenggunaDanPeran314::class, 'peran'])->name('pengguna.peran');
        Route::patch('/pengguna/{pengguna}/status', [ManajemenPenggunaDanPeran314::class, 'status'])->name('pengguna.status');
    });
});
