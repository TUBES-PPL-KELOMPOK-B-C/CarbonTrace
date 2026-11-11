<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// PBI-11 Pengingat Pencatatan Harian (Alvin-022): periksa pengingat yang jatuh tempo setiap menit.
// Di laptop jalankan "php artisan schedule:work"; di server cukup satu cron untuk schedule:run.
Schedule::command('pengingat:kirim')->everyMinute()->withoutOverlapping();
