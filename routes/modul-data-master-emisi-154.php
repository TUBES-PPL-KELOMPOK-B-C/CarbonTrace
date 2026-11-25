<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmissionFactorController;
use App\Http\Controllers\ConversionUnitController;

Route::middleware(['auth'])->prefix('admin')->group(function () {
    // PBI-07 (SCRUM-8): Manajemen Kategori Emisi
    Route::resource('categories', CategoryController::class)->except(['show']);

    // PBI-08 (SCRUM-9): Manajemen Faktor Emisi
    Route::resource('emission-factors', EmissionFactorController::class)->except(['show']);

    // PBI-09 (SCRUM-10): Manajemen Acuan Konversi Satuan (Porsi & Daya Perangkat Listrik)
    Route::get('conversion-units', [ConversionUnitController::class, 'index'])->name('conversion-units.index');

    Route::get('portion-units/create', [ConversionUnitController::class, 'createPortion'])->name('portion-units.create');
    Route::post('portion-units', [ConversionUnitController::class, 'storePortion'])->name('portion-units.store');
    Route::get('portion-units/{portionUnit}/edit', [ConversionUnitController::class, 'editPortion'])->name('portion-units.edit');
    Route::put('portion-units/{portionUnit}', [ConversionUnitController::class, 'updatePortion'])->name('portion-units.update');
    Route::delete('portion-units/{portionUnit}', [ConversionUnitController::class, 'destroyPortion'])->name('portion-units.destroy');

    Route::get('appliances/create', [ConversionUnitController::class, 'createAppliance'])->name('appliances.create');
    Route::post('appliances', [ConversionUnitController::class, 'storeAppliance'])->name('appliances.store');
    Route::get('appliances/{appliance}/edit', [ConversionUnitController::class, 'editAppliance'])->name('appliances.edit');
    Route::put('appliances/{appliance}', [ConversionUnitController::class, 'updateAppliance'])->name('appliances.update');
    Route::delete('appliances/{appliance}', [ConversionUnitController::class, 'destroyAppliance'])->name('appliances.destroy');
});