<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/modul-edukasi-dan-komunitas-365.php';

Route::get('/', function () {
    return view('publik.beranda'); // SEMENTARA untuk uji
});
