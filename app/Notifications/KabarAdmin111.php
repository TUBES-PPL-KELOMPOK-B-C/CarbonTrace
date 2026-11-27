<?php

namespace App\Notifications;

use App\Models\User314;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as Pengirim;
use Throwable;

// Lonceng notifikasi administrator (Alvin-111): kabar tentang pengguna, misalnya pendaftar baru
// atau pengguna yang menyelesaikan kuis awal. Dikirim ke semua akun administrator yang aktif.
class KabarAdmin111 extends Notification
{
    public function __construct(
        public readonly string $judul,
        public readonly string $pesan,
        public readonly ?string $tautan = null,
    ) {}

    public static function kirim(string $judul, string $pesan, ?string $tautan = null): void
    {
        try {
            $admin = User314::where('is_active', true)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['administrator', 'admin']))
                ->get();

            Pengirim::send($admin, new self($judul, $pesan, $tautan));
        } catch (Throwable $e) {
            // Kabar untuk admin tidak boleh menggagalkan pendaftaran atau kuis pengguna.
            report($e);
        }
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['jenis' => 'admin', 'judul' => $this->judul, 'pesan' => $this->pesan, 'tautan' => $this->tautan];
    }
}
