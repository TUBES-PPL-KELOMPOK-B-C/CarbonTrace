<?php

use App\Http\Controllers\ManajemenProfilPengguna314;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/profil', [ManajemenProfilPengguna314::class, 'index'])->name('profil.index');
    Route::put('/profil', [ManajemenProfilPengguna314::class, 'update'])->name('profil.update');
    Route::put('/profil/sandi', [ManajemenProfilPengguna314::class, 'sandi'])->name('profil.sandi');
    Route::delete('/profil', [ManajemenProfilPengguna314::class, 'destroy'])->name('profil.destroy');
});
