<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Lonceng notifikasi di bilah atas (fitur pendukung, Alvin-111). Daftarnya ditampilkan oleh
// layouts.aplikasi-111; controller ini hanya menandai sudah dibaca.
class Notifikasi111 extends Controller
{
    // Membuka satu notifikasi: ditandai dibaca, lalu menuju halaman yang terkait.
    public function buka(Request $request, string $notifikasi): RedirectResponse
    {
        $catatan = $request->user()->notifications()->findOrFail($notifikasi);
        $catatan->markAsRead();

        $tautan = $catatan->data['tautan'] ?? null;

        // Hanya jalur di situs ini, supaya notifikasi tidak bisa dipakai mengalihkan ke situs lain.
        return is_string($tautan) && str_starts_with($tautan, '/') && ! str_starts_with($tautan, '//')
            ? redirect($tautan)
            : redirect()->route('dashboard.index');
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
