<?php

use App\Http\Controllers\ManajemenProfilPengguna314;
use App\Http\Controllers\PengaturanKonsumsiRumahTangga314;
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
});
