<?php

use App\Http\Controllers\KuisAwal111;
use App\Http\Middleware\PastikanAdministrator314;
use Illuminate\Support\Facades\Route;

// KF-25 Pengisian Kuis Awal dan Garis Dasar (Alvin-111). Soalnya dari Bank Soal Kuis (PBI-06).
// Nama rute memakai "kuis-awal." karena "kuis." sudah dipakai Bank Soal Kuis.
Route::middleware('auth')->group(function () {
    Route::get('/kuis-awal', [KuisAwal111::class, 'mulai'])->name('kuis-awal.mulai');
    Route::post('/kuis-awal/jawab', [KuisAwal111::class, 'jawab'])->name('kuis-awal.jawab');
    Route::post('/kuis-awal/lewati', [KuisAwal111::class, 'lewati'])->name('kuis-awal.lewati');
    Route::get('/kuis-awal/hasil', [KuisAwal111::class, 'hasil'])->name('kuis-awal.hasil');

    Route::middleware(PastikanAdministrator314::class)
        ->get('/admin/kuis-awal', [KuisAwal111::class, 'ringkasan'])->name('kuis-awal.ringkasan');
});
