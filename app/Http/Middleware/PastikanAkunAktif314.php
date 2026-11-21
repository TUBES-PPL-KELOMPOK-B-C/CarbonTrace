<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PastikanAkunAktif314
{
    /**
     * Memastikan pengguna yang sedang masuk masih berstatus aktif.
     * Jika akun dinonaktifkan dari perangkat lain, sesi ini langsung diputus.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'pesan' => 'Akun kamu telah dinonaktifkan. Sesi diakhiri.',
                ], 401);
            }

            return redirect()->route('auth.masuk')->withErrors([
                'email' => 'Akun kamu sudah dinonaktifkan. Hubungi administrator jika ingin mengaktifkannya kembali.',
            ]);
        }

        return $next($request);
    }
}
