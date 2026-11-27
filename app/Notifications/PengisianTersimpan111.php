<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// Lonceng notifikasi: satu catatan setiap kali pengguna berhasil menyimpan, mengubah, atau
// menghapus data. Dibuat oleh middleware CatatNotifikasiPengisian111 dari pesan sukses halaman.
class PengisianTersimpan111 extends Notification
{
    public function __construct(
        public readonly string $judul,
        public readonly string $pesan,
        public readonly ?string $tautan = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['jenis' => 'pengisian', 'judul' => $this->judul, 'pesan' => $this->pesan, 'tautan' => $this->tautan];
    }
}
