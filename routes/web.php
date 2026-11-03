<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('publik.beranda'); // SEMENTARA untuk uji
});

// Modul 2: Pencatatan Aktivitas (Hadi Dwi Pranoto - 102022400221)
require __DIR__ . '/modul-pencatatan-aktivitas-221.php';
