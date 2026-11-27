<?php

use App\Http\Controllers\Notifikasi111;
use Illuminate\Support\Facades\Route;

// Lonceng notifikasi di bilah atas (fitur pendukung, Alvin-111).
// Pengingat otomatis 24 jam dikirim oleh perintah notifikasi:pengingat-otomatis (routes/console.php).
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi/{notifikasi}', [Notifikasi111::class, 'buka'])->whereUuid('notifikasi')->name('notifikasi.buka');
    Route::post('/notifikasi/baca-semua', [Notifikasi111::class, 'bacaSemua'])->name('notifikasi.baca-semua');
});
