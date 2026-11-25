<?php

use App\Http\Controllers\LaporanJejakKarbon022;
use App\Http\Controllers\ManajemenTargetEmisiBulanan022;
use App\Http\Controllers\PengingatPencatatanHarian022;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Target, Pengingat, dan Laporan (Alvin Hibatullah - 102022430022)
| PBI-10 Manajemen Target Emisi Bulanan
| PBI-11 Pengingat Pencatatan Harian
| PBI-12 Laporan Jejak Karbon
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // PBI-10 (KF-11)
    Route::get('/target-emisi', [ManajemenTargetEmisiBulanan022::class, 'index'])->name('target.index');
    Route::post('/target-emisi', [ManajemenTargetEmisiBulanan022::class, 'store'])->name('target.store');
    Route::put('/target-emisi/{target}', [ManajemenTargetEmisiBulanan022::class, 'update'])->name('target.update');
    Route::delete('/target-emisi/{target}', [ManajemenTargetEmisiBulanan022::class, 'destroy'])->name('target.destroy');
    Route::get('/api/target/berjalan', [ManajemenTargetEmisiBulanan022::class, 'berjalan'])->name('target.berjalan');

    // PBI-11 (KF-12)
    Route::get('/pengingat', [PengingatPencatatanHarian022::class, 'index'])->name('pengingat.index');
    Route::post('/pengingat', [PengingatPencatatanHarian022::class, 'store'])->name('pengingat.store');
    Route::put('/pengingat/{pengingat}', [PengingatPencatatanHarian022::class, 'update'])->name('pengingat.update');
    Route::patch('/pengingat/{pengingat}/saklar', [PengingatPencatatanHarian022::class, 'saklar'])->name('pengingat.saklar');
    Route::delete('/pengingat/{pengingat}', [PengingatPencatatanHarian022::class, 'destroy'])->name('pengingat.destroy');

    // PBI-12 (KF-13)
    Route::get('/laporan', [LaporanJejakKarbon022::class, 'index'])->name('laporan.index');
    Route::get('/laporan/susun', [LaporanJejakKarbon022::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanJejakKarbon022::class, 'store'])->name('laporan.store');
    Route::get('/laporan/{laporan}', [LaporanJejakKarbon022::class, 'show'])->whereNumber('laporan')->name('laporan.show');
    Route::put('/laporan/{laporan}', [LaporanJejakKarbon022::class, 'update'])->whereNumber('laporan')->name('laporan.update');
    Route::delete('/laporan/{laporan}', [LaporanJejakKarbon022::class, 'destroy'])->whereNumber('laporan')->name('laporan.destroy');
    Route::post('/api/laporan/pratinjau', [LaporanJejakKarbon022::class, 'pratinjau'])->name('laporan.pratinjau');
});
