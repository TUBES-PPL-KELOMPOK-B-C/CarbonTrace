<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('publik.beranda'); // SEMENTARA untuk uji
});

// ===========================================================================
// RUTE DEV-ONLY: Hanya untuk preview lokal - HAPUS sebelum push ke production
// ===========================================================================
if (app()->environment('local')) {
    // Login sebagai pengguna biasa
    Route::get('/dev-login-pengguna', function () {
        $user = \App\Models\User::where('email', 'pengguna@carbontrace.id')->first();
        Auth::login($user);
        return redirect('/aktivitas')->with('sukses', 'Login dev sebagai pengguna.');
    })->name('dev.login.pengguna');

    // Login sebagai admin
    Route::get('/dev-login-admin', function () {
        $user = \App\Models\User::where('email', 'admin@carbontrace.id')->first();
        Auth::login($user);
        return redirect('/admin/bank-kuis')->with('sukses', 'Login dev sebagai admin.');
    })->name('dev.login.admin');

    // Logout
    Route::get('/dev-logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('dev.logout');
}

// Modul 2: Pencatatan Aktivitas (Hadi Dwi Pranoto - 102022400221)
require __DIR__ . '/modul-pencatatan-aktivitas-221.php';
