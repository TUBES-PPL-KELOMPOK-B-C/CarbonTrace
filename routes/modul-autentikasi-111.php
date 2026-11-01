<?php

use App\Http\Controllers\Autentikasi111;
use App\Http\Controllers\LupaSandi111;
use Illuminate\Support\Facades\Route;

// KF-01 Registrasi, Login, Logout, dan Lupa kata sandi. Masuk lewat Google belum dibuat.
// Batas 5 kali salah sandi lalu dikunci 1 menit diatur di controller (per email + IP).
// throttle di sini hanya pengaman cadangan per IP supaya server tidak kebanjiran permintaan.
// Awalan pada throttle memisahkan batasnya dari rute lain; versi halaman dan JSON berbagi batas.
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [Autentikasi111::class, 'tampilMasuk'])->name('auth.masuk');
    Route::post('/masuk', [Autentikasi111::class, 'prosesMasuk'])
        ->middleware('throttle:30,1,masuk')
        ->name('auth.proses');

    Route::get('/daftar', [Autentikasi111::class, 'tampilDaftar'])->name('auth.daftar');
    Route::post('/daftar', [Autentikasi111::class, 'simpanDaftar'])
        ->middleware('throttle:5,1,daftar')
        ->name('auth.simpan');

    Route::get('/lupa-sandi', [LupaSandi111::class, 'tampilLupa'])->name('auth.lupa');
    Route::post('/lupa-sandi', [LupaSandi111::class, 'kirimTautan'])
        ->middleware('throttle:5,1,lupa')
        ->name('auth.kirim');

    // Tujuan tautan di email pemulihan
    Route::get('/atur-ulang-sandi/{token}', [LupaSandi111::class, 'tampilAtur'])->name('auth.atur.form');
    Route::post('/atur-ulang-sandi', [LupaSandi111::class, 'simpanSandi'])
        ->middleware('throttle:5,1,atur')
        ->name('auth.atur');
});

Route::post('/keluar', [Autentikasi111::class, 'keluar'])
    ->middleware('auth')
    ->name('auth.keluar');

// Versi JSON untuk dicoba lewat Postman atau curl. Sesi tetap memakai cookie, jadi simpan
// cookie dari /api/masuk lalu kirim lagi ke /api/saya dan /api/keluar.
Route::prefix('api')->name('auth.api.')->group(function () {
    Route::post('/masuk', [Autentikasi111::class, 'apiMasuk'])->middleware('throttle:30,1,masuk')->name('masuk');
    Route::post('/daftar', [Autentikasi111::class, 'apiDaftar'])->middleware('throttle:5,1,daftar')->name('daftar');
    Route::post('/lupa-sandi', [LupaSandi111::class, 'apiKirim'])->middleware('throttle:5,1,lupa')->name('lupa');
    Route::post('/atur-ulang-sandi', [LupaSandi111::class, 'apiAtur'])->middleware('throttle:5,1,atur')->name('atur');

    Route::middleware('auth')->group(function () {
        Route::get('/saya', [Autentikasi111::class, 'apiSaya'])->name('saya');
        Route::post('/keluar', [Autentikasi111::class, 'apiKeluar'])->name('keluar');
    });
});
