
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManajemenArtikelEdukasi365;

Route::middleware(['auth'])
    ->prefix('admin/artikel')
    ->name('artikel-edukasi.')
    ->group(function () {
        Route::get('/', [ManajemenArtikelEdukasi365::class, 'index'])
            ->name('index');

        Route::get('/create', [ManajemenArtikelEdukasi365::class, 'create'])
            ->name('create');

        Route::post('/', [ManajemenArtikelEdukasi365::class, 'store'])
            ->name('store');

        Route::get('/{artikel}/edit', [ManajemenArtikelEdukasi365::class, 'edit'])
            ->name('edit');

        Route::put('/{artikel}', [ManajemenArtikelEdukasi365::class, 'update'])
            ->name('update');

        Route::delete('/{artikel}', [ManajemenArtikelEdukasi365::class, 'destroy'])
            ->name('destroy');
    });
