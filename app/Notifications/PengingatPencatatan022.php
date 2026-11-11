<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

// PBI-11: pesan pengingat harian, dikirim lewat email.
class PengingatPencatatan022 extends Notification
{
    use Queueable;

    public function __construct(public readonly string $jam) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tujuan = Route::has('aktivitas.create') ? route('aktivitas.create') : route('dashboard.index');

        return (new MailMessage)
            ->subject('Pengingat: catat aktivitasmu hari ini')
            ->greeting('Halo, '.str($notifiable->name)->before(' ').'!')
            ->line('Sudah pukul '.$this->jam.' dan kamu belum mencatat aktivitas hari ini.')
            ->line('Catatan yang rutin membuat tren emisimu lebih mudah dibaca.')
            ->action('Catat Aktivitas', $tujuan)
            ->line('Pengingat ini dilewati pada hari kamu sudah mencatat. Jadwalnya bisa diubah di halaman Pengingat.')
            ->salutation('Salam, Tim CarbonTrace');
    }
}
