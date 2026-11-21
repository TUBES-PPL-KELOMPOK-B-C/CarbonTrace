<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmissionFactorController;

Route::middleware(['auth'])->prefix('admin')->group(function () {
    // PBI-07 (SCRUM-8): Manajemen Kategori Emisi
    Route::resource('categories', CategoryController::class)->except(['show']);

    // PBI-08 (SCRUM-9): Manajemen Faktor Emisi
    Route::resource('emission-factors', EmissionFactorController::class)->except(['show']);
});