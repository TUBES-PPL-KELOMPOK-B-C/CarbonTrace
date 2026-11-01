<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tamu yang membuka halaman di balik auth dipantulkan ke halaman masuk (KF-01);
        // pengguna yang sudah masuk tidak bisa membuka halaman masuk/daftar lagi (ke dashboard).
        $middleware->redirectGuestsTo(fn () => route('auth.masuk'));
        $middleware->redirectUsersTo(fn () => route('dashboard.index'));

        // Rute JSON yang bisa dicoba lewat Postman atau curl tanpa token CSRF. Masuk, daftar,
        // dan keluar hanya menerima badan JSON, yang tidak bisa dikirim formulir situs lain.
        // Rute api lain sengaja tidak dibebaskan.
        $middleware->preventRequestForgery(except: [
            'api/masuk',
            'api/daftar',
            'api/keluar',
            'api/lupa-sandi',
            'api/atur-ulang-sandi',
            'api/kalkulator',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Melewati batas laju: pesan ramah, bukan halaman 429 mentah.
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            $pesan = 'Terlalu banyak percobaan. Tunggu satu menit.';

            if ($request->is('api/*')) {
                return response()->json(['pesan' => $pesan], 429, $e->getHeaders());
            }

            if ($request->routeIs('kalkulator.hitung')) {
                return back()->withInput()->with('galat_kalkulator', $pesan);
            }

            if ($request->routeIs('auth.proses')) {
                return back()->withInput($request->only('email', 'ingat'))->with('galat_masuk', $pesan);
            }

            if ($request->routeIs('auth.kirim', 'auth.atur')) {
                return back()->withInput($request->only('email'))->withErrors(['email' => $pesan]);
            }

            if ($request->routeIs('auth.simpan')) {
                return back()->withInput($request->except('password', 'password_confirmation'))->withErrors(['email' => $pesan]);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['pesan' => 'Kamu belum masuk. Masuk dulu lewat /api/masuk.'], 401);
            }
        });
    })->create();
