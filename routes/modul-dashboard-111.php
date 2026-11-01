<?php

use App\Http\Controllers\Dashboard111;
use Illuminate\Support\Facades\Route;

// KF-21 Dashboard Jejak Karbon. Hanya untuk pengguna yang sudah masuk.
Route::get('/dashboard', [Dashboard111::class, 'index'])
    ->middleware('auth')
    ->name('dashboard.index');
