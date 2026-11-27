<?php

namespace App\Console\Commands;

use App\Models\User314;
use App\Notifications\PengingatOtomatis111;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Lonceng notifikasi (fitur pendukung, Alvin-111): pengingat otomatis bagi pengguna yang belum
// mencatat aktivitas selama 24 jam. Dijalankan penjadwal setiap jam (routes/console.php), jadi
// pengingat datang paling lambat satu jam setelah 24 jam terlewati, dan paling sering sekali per 24 jam.
// Berbeda dari PBI-11 (jam diatur pengguna, lewat email), pengingat ini tidak perlu diatur.
class KirimPengingatOtomatis111 extends Command
{
    protected $signature = 'notifikasi:pengingat-otomatis';

    protected $description = 'Kirim pengingat ke lonceng bagi pengguna yang 24 jam belum mencatat aktivitas';

    public function handle(): int
    {
        if (! Schema::hasTable('notifications')) {
            $this->warn('Tabel notifications belum ada. Jalankan php artisan migrate.');

            return self::SUCCESS;
        }

        $batas = now()->subDay();

        // Catatan terakhir per pengguna; baris lama tanpa created_at memakai tanggal aktivitasnya.
        $terakhirCatat = Schema::hasTable('activities')
            ? DB::table('activities')->selectRaw('user_id, max(coalesce(created_at, activity_date)) as terakhir')
                ->groupBy('user_id')->pluck('terakhir', 'user_id')
            : collect();

        $terakhirIngat = DB::table('notifications')
            ->where('type', PengingatOtomatis111::class)
            ->where('notifiable_type', (new User314)->getMorphClass())
            ->selectRaw('notifiable_id, max(created_at) as terakhir')
            ->groupBy('notifiable_id')->pluck('terakhir', 'notifiable_id');

        $terkirim = 0;

        User314::query()
            ->where('is_active', true)
            ->where('created_at', '<=', $batas)
            // Administrator tidak mencatat aktivitas harian, jadi tidak diingatkan.
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['administrator', 'admin']))
            ->each(function (User314 $pengguna) use ($batas, $terakhirCatat, $terakhirIngat, &$terkirim) {
                $catat = $terakhirCatat[$pengguna->id] ?? null;
                $ingat = $terakhirIngat[$pengguna->id] ?? null;

                if (($catat && $catat > $batas->toDateTimeString()) || ($ingat && $ingat > $batas->toDateTimeString())) {
                    return;
                }

                $pengguna->notify(new PengingatOtomatis111);
                $terkirim++;
            });

        $this->info("{$terkirim} pengingat otomatis terkirim.");

        return self::SUCCESS;
    }
}
