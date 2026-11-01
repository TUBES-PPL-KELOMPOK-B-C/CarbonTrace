<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/modul-autentikasi-111.php';
require __DIR__.'/modul-kalkulator-publik-111.php';
require __DIR__.'/modul-dashboard-111.php';
require __DIR__.'/modul-edukasi-dan-komunitas-365.php';

Route::view('/', 'publik.beranda')->name('beranda');
