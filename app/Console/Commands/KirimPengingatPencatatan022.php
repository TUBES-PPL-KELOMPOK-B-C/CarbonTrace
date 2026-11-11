<?php

namespace App\Console\Commands;

use App\Models\Reminder022;
use App\Notifications\PengingatPencatatan022;
use App\Services\RingkasanTarget022;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// PBI-11: dijalankan penjadwal setiap menit (routes/console.php). Mengirim pengingat yang jatuh
// tepat pada menit ini, dalam waktu Asia/Jakarta. Pengingat yang terlewat saat penjadwal mati
// tidak dikirim susulan: yang diperiksa hanya menit berjalan.
class KirimPengingatPencatatan022 extends Command
{
    protected $signature = 'pengingat:kirim';

    protected $description = 'Kirim pengingat pencatatan harian yang jatuh tempo pada menit ini (PBI-11)';

    public function handle(): int
    {
        $sekarang = Carbon::now(RingkasanTarget022::ZONA_WAKTU)->startOfMinute();
        $hariIni = $sekarang->toDateString();
        $terkirim = 0;

        $jatuhTempo = Reminder022::with('user')
            ->where('is_active', true)
            ->where('remind_time', $sekarang->format('H:i:00'))
            // Akun yang dinonaktifkan ikut berhenti menerima pengingat.
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get();

        foreach ($jatuhTempo as $pengingat) {
            if ($this->sudahMencatat($pengingat->user_id, $sekarang)) {
                continue;
            }

            // Penjaga supaya pengingat yang sama tidak terkirim dua kali bila perintah berjalan ganda.
            if (! Cache::add("pengingat-022:{$pengingat->id}:{$hariIni}", true, now()->addDay())) {
                continue;
            }

            $pengingat->user->notify(new PengingatPencatatan022(str_replace(':', '.', $pengingat->jam())));
            $terkirim++;
        }

        $this->info("{$terkirim} pengingat terkirim untuk pukul {$sekarang->format('H:i')}.");

        return self::SUCCESS;
    }

    // Pengguna dianggap sudah mencatat bila ada aktivitas bertanggal hari ini, atau ada catatan
    // yang dibuat hari ini (misalnya mencatat aktivitas kemarin).
    private function sudahMencatat(int $penggunaId, Carbon $sekarang): bool
    {
        if (! Schema::hasTable('activities')) {
            return false;
        }

        return DB::table('activities')
            ->where('user_id', $penggunaId)
            ->where(fn ($q) => $q
                ->where('activity_date', $sekarang->toDateString())
                ->orWhere('created_at', '>=', $sekarang->copy()->startOfDay()->utc()))
            ->exists();
    }
}
