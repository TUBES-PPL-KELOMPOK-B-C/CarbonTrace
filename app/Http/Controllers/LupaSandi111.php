<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

// KF-01 Lupa kata sandi: kirim tautan pemulihan ke email, lalu buat kata sandi baru lewat tautan itu.
// Tautan berlaku 60 menit dan hanya bisa dipakai sekali (tokennya dihapus setelah dipakai).
class LupaSandi111 extends Controller
{
    private const PESAN_TERKIRIM = 'Tautan untuk membuat kata sandi baru telah dikirim ke email Anda. Silakan periksa kotak masuk dan folder spam.';
    private const PESAN_MODE_LOG = 'Mode pengembangan: email tidak dikirim. Silakan lanjutkan lewat tombol di bawah ini.';
    private const PESAN_GAGAL_KIRIM = 'Email pemulihan belum dapat dikirim. Silakan coba beberapa saat lagi.';
    private const PESAN_BELUM_TERDAFTAR = 'Email tersebut belum terdaftar di CarbonTrace. Silakan periksa kembali alamat email Anda atau daftar terlebih dahulu.';
    private const PESAN_NONAKTIF = 'Akun dengan email tersebut telah dinonaktifkan. Silakan hubungi administrator.';
    private const PESAN_TERLALU_SERING = 'Tautan pemulihan baru saja dikirim. Silakan tunggu satu menit sebelum meminta tautan baru.';
    private const PESAN_BERHASIL = 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru Anda.';
    private const PESAN_TAUTAN_SALAH = 'Tautan pemulihan tidak valid atau telah kedaluwarsa. Silakan minta tautan baru.';

    // Tautan pemulihan dari email terakhir, untuk ditampilkan di halaman pada mode pengembangan.
    private ?string $tautanPemulihan = null;

    public function tampilLupa(): View
    {
        return view('auth.lupa-sandi-111');
    }

    public function kirimTautan(Request $request): RedirectResponse
    {
        if ($galat = $this->kirim($request)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => $galat[0]]);
        }

        return back()->withInput($request->only('email'))->with('status', $this->pesanTerkirim())
            ->with('tautan_lokal', $this->tautanLokal());
    }

    public function tampilAtur(Request $request, string $token): View
    {
        return view('auth.atur-ulang-sandi-111', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function simpanSandi(Request $request): RedirectResponse
    {
        if ($galat = $this->atur($request)) {
            return back()->withInput($request->only('email'))->with('galat_atur', $galat);
        }

        return redirect()->route('auth.masuk')->with('status', self::PESAN_BERHASIL);
    }

    // ---- Versi JSON, untuk dicoba lewat Postman atau curl ----

    public function apiKirim(Request $request): JsonResponse
    {
        if (! $request->isJson()) {
            return $this->bukanJson();
        }

        if ($galat = $this->kirim($request)) {
            return response()->json(['pesan' => $galat[0]], $galat[1]);
        }

        return response()->json(array_filter([
            'pesan' => $this->pesanTerkirim(),
            'tautan_pengembangan' => $this->tautanLokal(),
        ]));
    }

    public function apiAtur(Request $request): JsonResponse
    {
        if (! $request->isJson()) {
            return $this->bukanJson();
        }

        if ($galat = $this->atur($request)) {
            return response()->json(['pesan' => $galat], 422);
        }

        return response()->json(['pesan' => self::PESAN_BERHASIL]);
    }

    // Mengembalikan null bila tautan terkirim, atau [pesan galat, kode HTTP untuk versi JSON].
    private function kirim(Request $request): ?array
    {
        $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar.',
        ]);

        // Email yang belum terdaftar dan akun nonaktif diberi peringatan yang jelas. Untuk
        // membatasi penebakan email, rute ini dibatasi 5 permintaan per menit per IP.
        $pengguna = User::where('email', $request->input('email'))->first();

        if (! $pengguna) {
            return [self::PESAN_BELUM_TERDAFTAR, 404];
        }

        if (! $pengguna->is_active) {
            return [self::PESAN_NONAKTIF, 403];
        }

        ResetPassword::toMailUsing(function (User $pengguna, string $token) {
            $this->tautanPemulihan = route('auth.atur.form', ['token' => $token, 'email' => $pengguna->email]);

            return (new MailMessage)
                ->subject('Atur Ulang Kata Sandi CarbonTrace')
                ->greeting('Yth. '.$pengguna->name.',')
                ->line('Kami menerima permintaan untuk membuat kata sandi baru bagi akun CarbonTrace Anda.')
                ->action('Buat Kata Sandi Baru', $this->tautanPemulihan)
                ->line('Tautan ini berlaku selama 60 menit dan hanya dapat digunakan satu kali.')
                ->line('Apabila Anda tidak merasa mengajukan permintaan ini, abaikan email ini. Kata sandi Anda tidak akan berubah.')
                ->salutation('Hormat kami, Tim CarbonTrace');
        });

        try {
            $status = Password::sendResetLink(['email' => $pengguna->email]);
        } catch (TransportExceptionInterface $e) {
            // Mis. App Password salah atau server email tidak terjangkau. Token dihapus supaya
            // pengguna bisa langsung mencoba lagi tanpa kena batas satu menit.
            Log::error('Email pemulihan kata sandi gagal dikirim', ['galat' => $e->getMessage()]);
            Password::deleteToken($pengguna);

            return [self::PESAN_GAGAL_KIRIM, 503];
        }

        return $status === Password::RESET_THROTTLED ? [self::PESAN_TERLALU_SERING, 429] : null;
    }

    // Selama MAIL_MAILER=log di laptop, email hanya ditulis ke log. Halaman tidak boleh mengatakan
    // email sudah terkirim, dan tautannya ditampilkan supaya alurnya tetap bisa dijalankan.
    private function modeLog(): bool
    {
        return app()->isLocal() && config('mail.default') === 'log';
    }

    private function pesanTerkirim(): string
    {
        return $this->modeLog() ? self::PESAN_MODE_LOG : self::PESAN_TERKIRIM;
    }

    // Tidak pernah berisi tautan di luar mode pengembangan: di server sungguhan, tautan pemulihan
    // hanya boleh sampai lewat email pemilik akun.
    private function tautanLokal(): ?string
    {
        return $this->modeLog() ? $this->tautanPemulihan : null;
    }

    // Mengembalikan null bila berhasil, atau pesan galat untuk pengguna.
    private function atur(Request $request): ?string
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required' => self::PESAN_TAUTAN_SALAH,
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $pengguna, string $sandi) {
                // Token "ingat saya" diganti supaya perangkat lain yang masih tersimpan ikut keluar.
                $pengguna->forceFill(['password' => $sandi, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($pengguna));
            },
        );

        return $status === Password::PASSWORD_RESET ? null : self::PESAN_TAUTAN_SALAH;
    }

    private function bukanJson(): JsonResponse
    {
        return response()->json(['pesan' => 'Kirim badan permintaan sebagai JSON (Content-Type: application/json).'], 415);
    }
}
