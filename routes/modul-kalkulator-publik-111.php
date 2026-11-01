<?php

use App\Http\Controllers\KalkulatorPublik111;
use Illuminate\Support\Facades\Route;

// KF-24 Kalkulator Jejak Karbon Publik. Terbuka tanpa akun, dibatasi 30 hitungan per menit per IP.
// Awalan "kalkulator" pada throttle memisahkan hitungannya dari batas masuk/daftar.
Route::get('/kalkulator', [KalkulatorPublik111::class, 'index'])->name('kalkulator.index');

Route::middleware('throttle:30,1,kalkulator')->group(function () {
    Route::post('/kalkulator/hitung', [KalkulatorPublik111::class, 'hitung'])->name('kalkulator.hitung');
    Route::post('/api/kalkulator', [KalkulatorPublik111::class, 'api'])->name('kalkulator.api');
});
