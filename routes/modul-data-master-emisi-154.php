<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

Route::middleware(['auth'])->prefix('admin')->group(function () {
    // PBI-07 (SCRUM-8): Manajemen Kategori Emisi
    Route::resource('categories', CategoryController::class)->except(['show']);
});