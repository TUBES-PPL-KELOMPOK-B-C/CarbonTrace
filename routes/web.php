<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/modul-autentikasi-111.php';
require __DIR__.'/modul-kalkulator-publik-111.php';
require __DIR__.'/modul-dashboard-111.php';
require __DIR__.'/modul-target-pengingat-dan-laporan-022.php';
require __DIR__.'/modul-edukasi-dan-komunitas-365.php';
require __DIR__.'/modul-pencatatan-aktivitas-221.php';
require __DIR__.'/modul-akun-dan-administrasi-pengguna-314.php';

Route::view('/', 'publik.beranda')->name('beranda');

// Redirect helper jika user/browser mengakses /login atau /register
Route::redirect('/login', '/masuk')->name('login');
Route::redirect('/register', '/daftar')->name('register');

// ===========================================================================
// RUTE DEV-ONLY: Hanya untuk preview lokal - HAPUS sebelum push ke production
// ===========================================================================
if (app()->environment('local')) {
    // Login sebagai pengguna biasa
    Route::get('/dev-login-pengguna', function () {
        $user = \App\Models\User314::where('email', 'pengguna@carbontrace.id')->first();
        if ($user) {
            Auth::login($user);
        }
        return redirect('/dashboard')->with('sukses', 'Login dev sebagai pengguna.');
    })->name('dev.login.pengguna');

    // Login sebagai admin
    Route::get('/dev-login-admin', function () {
        $user = \App\Models\User314::where('email', 'admin@carbontrace.id')->first();
        if ($user) {
            Auth::login($user);
        }
        return redirect('/dashboard')->with('sukses', 'Login dev sebagai admin.');
    })->name('dev.login.admin');

    // Logout
    Route::get('/dev-logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('dev.logout');
}
