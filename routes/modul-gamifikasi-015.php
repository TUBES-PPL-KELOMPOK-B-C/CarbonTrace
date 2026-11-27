<?php

use App\Http\Controllers\ManajemenLencana015;
use App\Http\Middleware\PastikanAdministrator314;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Gamifikasi (Man Rezki Setiawan - 102022430015)
| PBI-13 Manajemen Lencana
| PBI-14 Manajemen Tantangan      -- belum dikerjakan
| PBI-15 Keikutsertaan Tantangan  -- belum dikerjakan
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // PBI-13 (KF-14): galeri lencana, terbuka untuk semua pengguna yang sudah masuk.
    Route::get('/lencana', [ManajemenLencana015::class, 'galeri'])->name('lencana.galeri');

    // PBI-13 (KF-14): pengelolaan lencana, khusus administrator.
    Route::middleware(PastikanAdministrator314::class)->prefix('admin')->group(function () {
        Route::get('/lencana', [ManajemenLencana015::class, 'index'])->name('lencana.index');
        Route::post('/lencana', [ManajemenLencana015::class, 'store'])->name('lencana.store');
        Route::put('/lencana/{lencana}', [ManajemenLencana015::class, 'update'])->name('lencana.update');
        Route::patch('/lencana/{lencana}/status', [ManajemenLencana015::class, 'status'])->name('lencana.status');
        Route::delete('/lencana/{lencana}', [ManajemenLencana015::class, 'destroy'])->name('lencana.destroy');
    });
});
