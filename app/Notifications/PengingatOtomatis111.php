<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

// Lonceng notifikasi: pengingat otomatis bila pengguna belum mencatat aktivitas selama 24 jam.
// Dikirim oleh perintah notifikasi:pengingat-otomatis (routes/console.php), paling sering sekali per 24 jam.
class PengingatOtomatis111 extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'jenis' => 'pengingat',
            'judul' => 'Pengingat harian',
            'pesan' => 'Sudah 24 jam sejak catatan terakhirmu. Yuk catat aktivitas hari ini supaya tren emisimu tetap lengkap.',
            'tautan' => Route::has('aktivitas.create') ? route('aktivitas.create', absolute: false) : route('dashboard.index', absolute: false),
        ];
    }
}
