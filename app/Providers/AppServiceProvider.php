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
        //
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
    }
}
