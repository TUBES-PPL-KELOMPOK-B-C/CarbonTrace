
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

        // Nama parameter harus sama dengan argumen controller ($article) supaya route model binding berjalan.
        Route::get('/{article}/edit', [ManajemenArtikelEdukasi365::class, 'edit'])
            ->name('edit');

        Route::put('/{article}', [ManajemenArtikelEdukasi365::class, 'update'])
            ->name('update');

        Route::delete('/{article}', [ManajemenArtikelEdukasi365::class, 'destroy'])
            ->name('destroy');
    });
