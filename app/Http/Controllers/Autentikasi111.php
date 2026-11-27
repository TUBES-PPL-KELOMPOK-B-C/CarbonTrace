<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanRegistrasi111;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class Autentikasi111 extends Controller
{
    private const PESAN_SALAH = 'Email atau kata sandi salah.';
    private const PESAN_NONAKTIF = 'Akun ini dinonaktifkan. Hubungi administrator.';
    private const PESAN_BUKAN_ADMIN = 'Akun ini bukan akun administrator. Pilih "Pengguna" untuk masuk.';
    private const PESAN_AKUN_ADMIN = 'Ini akun administrator. Pilih "Administrator" untuk masuk.';
    private const PERAN_BAWAAN = 'user';
    private const BATAS_GAGAL = 5;
    private const LAMA_KUNCI_DETIK = 60;

    public function tampilMasuk(Request $request): View
    {
        // Diisi oleh simpanDaftar; diambil sekali supaya kolom email langsung terisi setelah mendaftar
        return view('auth.masuk-111', ['emailBaru' => $request->session()->pull('email_baru')]);
    }

    public function prosesMasuk(Request $request): RedirectResponse
    {
        if ($galat = $this->cobaMasuk($request)) {
            return back()
                ->withInput($request->only('email', 'ingat', 'sebagai'))
                ->with('galat_masuk', $galat[0]);
        }

        // KF-25: pengguna yang belum pernah mengisi (dan tidak memilih melewati) diarahkan ke kuis awal.
        if (app(\App\Services\KuisAwal111::class)->perluDiisi($request->user())) {
            return redirect()->route('kuis-awal.mulai');
        }

        return redirect()->intended(route('dashboard.index'));
    }

    public function tampilDaftar(): View
    {
        return view('auth.daftar-111');
    }

    public function simpanDaftar(SimpanRegistrasi111 $request): RedirectResponse
    {
        $this->buatAkun($request);

        // Pendaftaran tidak langsung memasukkan pengguna: kembali ke beranda, lalu masuk sendiri.
        // Email disimpan di sesi sampai halaman masuk dibuka, supaya kolomnya sudah terisi.
        $request->session()->put('email_baru', $request->validated('email'));

        return redirect()->route('beranda')->with('sukses', 'Akun berhasil dibuat. Silakan masuk untuk mulai mencatat.');
    }

    public function keluar(Request $request): RedirectResponse
    {
        $this->akhiriSesi($request);

        return redirect()->route('beranda');
    }

    // ---- Versi JSON, untuk dicoba lewat Postman atau curl. Sesinya tetap memakai cookie. ----

    public function apiMasuk(Request $request): JsonResponse
    {
        if ($tolak = $this->wajibJson($request) ?? $this->tolakJikaSudahMasuk()) {
            return $tolak;
        }

        if ($galat = $this->cobaMasuk($request)) {
            return response()->json(['pesan' => $galat[0]], $galat[1]);
        }

        return response()->json(['pesan' => 'Berhasil masuk.', 'pengguna' => Auth::user()]);
    }

    public function apiDaftar(SimpanRegistrasi111 $request): JsonResponse
    {
        if ($tolak = $this->wajibJson($request) ?? $this->tolakJikaSudahMasuk()) {
            return $tolak;
        }

        return response()->json(['pesan' => 'Akun berhasil dibuat.', 'pengguna' => $this->buatAkun($request)->refresh()], 201);
    }

    public function apiKeluar(Request $request): JsonResponse
    {
        if ($tolak = $this->wajibJson($request)) {
            return $tolak;
        }

        $this->akhiriSesi($request);

        return response()->json(['pesan' => 'Berhasil keluar.']);
    }

    public function apiSaya(Request $request): JsonResponse
    {
        return response()->json(['pengguna' => $request->user()]);
    }

    // Mengembalikan null bila berhasil masuk, atau [pesan galat, kode HTTP untuk versi JSON].
    private function cobaMasuk(Request $request): ?array
    {
        $kredensial = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            // Pilihan "Masuk sebagai" di halaman masuk. Opsional, supaya versi JSON lama tetap jalan.
            'sebagai' => ['nullable', 'in:pengguna,administrator'],
        ], [
            'sebagai.in' => 'Pilihan masuk tidak dikenal.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $sebagai = $kredensial['sebagai'] ?? null;
        unset($kredensial['sebagai']);

        // 5 kali salah untuk email yang sama dari IP yang sama: dikunci 1 menit.
        // Hanya percobaan gagal yang dihitung, dan hitungan direset setelah berhasil masuk.
        $kunci = 'masuk|'.Str::lower($kredensial['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_GAGAL)) {
            return ['Terlalu banyak percobaan masuk. Silakan coba lagi dalam '.RateLimiter::availableIn($kunci).' detik.', 429];
        }

        // Satu pesan untuk email yang belum terdaftar atau sandi yang salah, tanpa menyebut mana yang salah.
        if (! Auth::attempt($kredensial, $request->boolean('ingat'))) {
            RateLimiter::hit($kunci, self::LAMA_KUNCI_DETIK);

            return [self::PESAN_SALAH, 401];
        }

        RateLimiter::clear($kunci);

        // Akun nonaktif ditolak dengan pesan tersendiri, bukan pesan sandi salah.
        if (! Auth::user()->is_active) {
            $this->akhiriSesi($request);

            return [self::PESAN_NONAKTIF, 403];
        }

        // Pilihan "Masuk sebagai" harus cocok dengan peran akun. Sandinya sudah benar, jadi tidak
        // dihitung sebagai percobaan gagal; pengguna cukup memilih jenis masuk yang tepat.
        if ($sebagai) {
            $admin = Auth::user()->hasAnyRole(['administrator', 'admin']);
            if ($admin !== ($sebagai === 'administrator')) {
                $this->akhiriSesi($request);

                return [$admin ? self::PESAN_AKUN_ADMIN : self::PESAN_BUKAN_ADMIN, 403];
            }
        }

        $request->session()->regenerate();

        return null;
    }

    private function buatAkun(SimpanRegistrasi111 $request): User
    {
        $data = $request->validated();

        // Satu transaksi supaya tidak ada akun tanpa peran. Nama peran bawaan "user" mengikuti
        // seeder Modul 2 (MasterDataEmisiStubSeeder); selama perannya belum dibuat, akun tetap jadi.
        $pengguna = DB::transaction(function () use ($data) {
            $role = Role::whereIn('name', ['user', 'pengguna'])->where('guard_name', 'web')->first()
                ?? Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);

            $pengguna = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'campus' => $data['campus'] ?? null,
                'password' => $data['password'],
                'role_id' => $role->id,
            ]);

            $pengguna->assignRole($role);

            return $pengguna;
        });

        // Lonceng administrator: kabar ada pendaftar baru, menuju baris akunnya di Manajemen Pengguna.
        \App\Notifications\KabarAdmin111::kirim(
            'Pengguna baru',
            "{$pengguna->name} ({$pengguna->email}) baru saja mendaftar.",
            route('pengguna.index', ['cari' => $pengguna->email], absolute: false),
        );

        return $pengguna;
    }

    private function akhiriSesi(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    // Rute JSON dibebaskan dari token CSRF (lihat bootstrap/app.php). Formulir dari situs lain
    // tidak bisa mengirim badan JSON, jadi permintaan selain JSON ditolak.
    private function wajibJson(Request $request): ?JsonResponse
    {
        return $request->isJson()
            ? null
            : response()->json(['pesan' => 'Kirim badan permintaan sebagai JSON (Content-Type: application/json).'], 415);
    }

    private function tolakJikaSudahMasuk(): ?JsonResponse
    {
        return Auth::check()
            ? response()->json(['pesan' => 'Kamu sudah masuk sebagai '.Auth::user()->email.'. Keluar dulu lewat /api/keluar.'], 409)
            : null;
    }
}
