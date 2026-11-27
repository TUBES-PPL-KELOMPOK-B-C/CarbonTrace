<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/modul-autentikasi-111.php';
require __DIR__.'/modul-kalkulator-publik-111.php';
require __DIR__.'/modul-dashboard-111.php';
require __DIR__.'/modul-notifikasi-111.php';
require __DIR__.'/modul-kuis-awal-111.php';
require __DIR__.'/modul-target-pengingat-dan-laporan-022.php';
require __DIR__.'/modul-edukasi-dan-komunitas-365.php';
require __DIR__.'/modul-pencatatan-aktivitas-221.php';
require __DIR__.'/modul-akun-dan-administrasi-pengguna-314.php';
require __DIR__.'/modul-gamifikasi-015.php';
require __DIR__.'/modul-data-master-emisi-154.php';

Route::view('/', 'publik.beranda')->name('beranda');

// Redirect helper jika user/browser mengakses /login atau /register
Route::redirect('/login', '/masuk')->name('login');
Route::redirect('/register', '/daftar')->name('register');
