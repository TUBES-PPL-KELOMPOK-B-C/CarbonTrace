<?php

namespace App\Http\Middleware;

use App\Notifications\PengisianTersimpan111;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

// Lonceng notifikasi (fitur pendukung, Alvin-111): setiap simpan, ubah, atau hapus yang berhasil
// dicatat sebagai notifikasi, tanpa mengubah controller modul lain. Patokannya pesan sukses yang
// sudah dipakai semua modul untuk toast (flash "sukses", "success", atau "status") pada permintaan
// POST/PUT/PATCH/DELETE yang dialihkan. Permintaan yang gagal validasi tidak membawa pesan sukses,
// jadi tidak ikut tercatat.
class CatatNotifikasiPengisian111
{
    private const KUNCI_PESAN = ['sukses', 'success', 'status'];

    // Rute yang tidak perlu dicatat: autentikasi dan lonceng itu sendiri.
    private const DIKECUALIKAN = ['auth.*', 'notifikasi.*'];

    // Awalan nama rute => judul notifikasi
    private const JUDUL = [
        'aktivitas' => 'Aktivitas',
        'template' => 'Template Rutin',
        'target' => 'Target Emisi',
        'pengingat' => 'Pengingat',
        'laporan' => 'Laporan',
        'profil' => 'Profil',
        'rumah' => 'Rumah Tangga',
        'categories' => 'Kategori Emisi',
        'emission-factors' => 'Faktor Emisi',
        'portion-units' => 'Konversi Satuan',
        'appliances' => 'Konversi Satuan',
        'lencana' => 'Lencana',
        'pengguna' => 'Manajemen Pengguna',
        'kuis' => 'Bank Soal Kuis',
        'artikel' => 'Edukasi',
        'kuis-awal' => 'Kuis Awal',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $respons = $next($request);

        if ($request->isMethodSafe() || ! $respons instanceof RedirectResponse || ! $request->user()
            || ! $request->hasSession() || $request->routeIs(...self::DIKECUALIKAN)) {
            return $respons;
        }

        // Hanya pesan yang di-flash pada permintaan ini, bukan sisa dari halaman sebelumnya.
        $baru = $request->session()->get('_flash.new', []);
        $kunci = collect(self::KUNCI_PESAN)->first(fn ($k) => in_array($k, $baru, true));
        $pesan = $kunci ? $request->session()->get($kunci) : null;

        if (! is_string($pesan) || trim($pesan) === '') {
            return $respons;
        }

        try {
            $request->user()->notify(new PengisianTersimpan111($this->judul($request), $pesan, $this->tautan($respons)));
        } catch (Throwable $e) {
            // Lonceng tidak boleh menggagalkan penyimpanan, misalnya bila tabel notifikasi belum dimigrasi.
            report($e);
        }

        return $respons;
    }

    private function judul(Request $request): string
    {
        $awalan = strtok((string) $request->route()?->getName(), '.');

        return self::JUDUL[$awalan] ?? 'CarbonTrace';
    }

    // Disimpan sebagai jalur relatif supaya tetap benar bila alamat server berubah.
    private function tautan(RedirectResponse $respons): ?string
    {
        $bagian = parse_url($respons->getTargetUrl());
        $jalur = ($bagian['path'] ?? '/').(isset($bagian['query']) ? '?'.$bagian['query'] : '');

        return $jalur === '' ? null : $jalur;
    }
}
