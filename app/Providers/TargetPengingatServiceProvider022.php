<?php

namespace App\Providers;

use App\Services\RingkasanTarget022;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

// Modul Target, Pengingat, dan Laporan (Alvin-022). Menyimak perubahan aktivitas dari Modul 2
// (model Activitie221 milik Hadi) tanpa mengubah kodenya, supaya event PemakaianTargetBerubah022
// terkirim setiap kali pemakaian target berubah.
class TargetPengingatServiceProvider022 extends ServiceProvider
{
    private const MODEL_AKTIVITAS = 'App\Models\Activitie221';

    public function boot(): void
    {
        if (! class_exists(self::MODEL_AKTIVITAS)) {
            return;
        }

        Event::listen(
            ['eloquent.saved: '.self::MODEL_AKTIVITAS, 'eloquent.deleted: '.self::MODEL_AKTIVITAS],
            function (object $aktivitas) {
                // Tanggal lama ikut diperiksa, karena koreksi tanggal bisa memindahkan emisi ke periode lain.
                $tanggal = [$aktivitas->activity_date, $aktivitas->getOriginal('activity_date')];

                app(RingkasanTarget022::class)->kabariPerubahan(
                    (int) $aktivitas->user_id,
                    array_map(fn ($t) => $t ? Carbon::parse($t)->toDateString() : null, $tanggal),
                );
            },
        );
    }
}
