<?php

namespace App\Providers;

use App\Models\Activitie221;
use App\Models\ActivityTemplate221;
use App\Models\QuizQuestion221;
use App\Policies\ManajemenBankPertanyaanKuisPolicy221;
use App\Policies\PencatatanAktivitasEmisiHarianPolicy221;
use App\Policies\TemplateAktivitasRutinPolicy221;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Laravel Dusk (tes browser, branch Test_Dusk): tidak didaftarkan otomatis, karena paketnya
        // membuka rute /_dusk/login tanpa kata sandi. Rute itu hanya ada di server tes Dusk
        // (APP_ENV=dusk); di konsol cukup perintah artisan dusk-nya saja.
        if (class_exists(\Laravel\Dusk\DuskServiceProvider::class)
            && ($this->app->environment('dusk') || $this->app->runningInConsole())) {
            $this->app->register(\Laravel\Dusk\DuskServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pemetaan Policy Modul 2 (Hadi-221)
        Gate::policy(Activitie221::class, PencatatanAktivitasEmisiHarianPolicy221::class);
        Gate::policy(ActivityTemplate221::class, TemplateAktivitasRutinPolicy221::class);
        Gate::policy(QuizQuestion221::class, ManajemenBankPertanyaanKuisPolicy221::class);

        // Pemetaan Policy Modul 1 (Rosi-314)
        Gate::policy(\App\Models\User314::class, \App\Policies\ManajemenProfilPenggunaPolicy314::class);
        Gate::policy(\App\Models\UserSetting314::class, \App\Policies\PengaturanKonsumsiRumahTanggaPolicy314::class);
        Gate::define('lihat-papan-peringkat', [\App\Policies\PapanPeringkatKomunitasPolicy314::class, 'viewAny']);

        // Pemetaan Policy Modul Gamifikasi (Man-015)
        Gate::policy(\App\Models\Badge015::class, \App\Policies\ManajemenLencanaPolicy015::class);
    }
}
