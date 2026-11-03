<?php

use App\Http\Controllers\ManajemenBankPertanyaanKuis221;
use App\Http\Controllers\PencatatanAktivitasEmisiHarian221;
use App\Http\Controllers\TemplateAktivitasRutin221;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Modul 2: Pencatatan Aktivitas (Hadi Dwi Pranoto - 102022400221)
| PBI-04 Pencatatan Aktivitas Emisi Harian
| PBI-05 Template Aktivitas Rutin
| PBI-06 Manajemen Bank Pertanyaan Kuis
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // ----------------------------------------------------------------------
    // PBI-04: Pencatatan Aktivitas Emisi Harian (KF-05)
    // ----------------------------------------------------------------------
    Route::get('/aktivitas', [PencatatanAktivitasEmisiHarian221::class, 'index'])->name('aktivitas.index');
    Route::get('/aktivitas/buat', [PencatatanAktivitasEmisiHarian221::class, 'create'])->name('aktivitas.create');
    Route::post('/aktivitas', [PencatatanAktivitasEmisiHarian221::class, 'store'])->name('aktivitas.store');
    Route::get('/aktivitas/{aktivitas}/ubah', [PencatatanAktivitasEmisiHarian221::class, 'edit'])->name('aktivitas.edit');
    Route::put('/aktivitas/{aktivitas}', [PencatatanAktivitasEmisiHarian221::class, 'update'])->name('aktivitas.update');
    Route::delete('/aktivitas/{aktivitas}', [PencatatanAktivitasEmisiHarian221::class, 'destroy'])->name('aktivitas.destroy');
    Route::post('/api/aktivitas/pratinjau', [PencatatanAktivitasEmisiHarian221::class, 'pratinjau'])->name('aktivitas.pratinjau');

    // ----------------------------------------------------------------------
    // PBI-05: Template Aktivitas Rutin (KF-06)
    // ----------------------------------------------------------------------
    Route::get('/template-aktivitas', [TemplateAktivitasRutin221::class, 'index'])->name('template.index');
    Route::post('/template-aktivitas', [TemplateAktivitasRutin221::class, 'store'])->name('template.store');
    Route::put('/template-aktivitas/{template}', [TemplateAktivitasRutin221::class, 'update'])->name('template.update');
    Route::delete('/template-aktivitas/{template}', [TemplateAktivitasRutin221::class, 'destroy'])->name('template.destroy');
    Route::post('/template-aktivitas/{template}/pakai', [TemplateAktivitasRutin221::class, 'pakai'])->name('template.pakai');

    // ----------------------------------------------------------------------
    // PBI-06: Manajemen Bank Pertanyaan Kuis (KF-07, Admin)
    // ----------------------------------------------------------------------
    Route::prefix('admin')->group(function () {
        Route::get('/bank-kuis', [ManajemenBankPertanyaanKuis221::class, 'index'])->name('kuis.index');
        Route::get('/bank-kuis/buat', [ManajemenBankPertanyaanKuis221::class, 'create'])->name('kuis.create');
        Route::post('/bank-kuis', [ManajemenBankPertanyaanKuis221::class, 'store'])->name('kuis.store');
        Route::get('/bank-kuis/{soal}/ubah', [ManajemenBankPertanyaanKuis221::class, 'edit'])->name('kuis.edit');
        Route::put('/bank-kuis/{soal}', [ManajemenBankPertanyaanKuis221::class, 'update'])->name('kuis.update');
        Route::patch('/bank-kuis/{soal}/status', [ManajemenBankPertanyaanKuis221::class, 'status'])->name('kuis.status');
        Route::delete('/bank-kuis/{soal}', [ManajemenBankPertanyaanKuis221::class, 'destroy'])->name('kuis.destroy');
    });
});
