@extends('layouts.publik-022')

@section('judul', 'Masuk — CarbonTrace')
@section('kelas-body', 'lp-auth')

@push('aset')
    @vite(['resources/css/autentikasi-111.css', 'resources/js/autentikasi-111.js'])
@endpush

@section('isi')
    @include('publik.beranda._latar-022')

    <a href="{{ route('beranda') }}" class="lp-auth__merek">
        <svg class="lp-lambang lp-lambang--kecil" aria-hidden="true" focusable="false"><use href="#ct-lambang"/></svg>
        <span>CARBONTRACE</span>
    </a>

    <main class="lp-auth__isi">
        <section class="lp-auth__kartu" aria-labelledby="judul-masuk">
            <span class="lp-auth__lambang" aria-hidden="true">
                <svg class="lp-lambang" focusable="false"><use href="#ct-lambang"/></svg>
            </span>
            <h1 class="lp-auth__judul" id="judul-masuk">Masuk ke CarbonTrace</h1>
            <p class="lp-auth__sub">Lanjutkan mencatat dan menurunkan jejak karbonmu.</p>

            @if (session('status'))
                <div class="lp-auth__info" role="status">{{ session('status') }}</div>
            @endif

            @if (session('galat_masuk'))
                <div class="lp-auth__galat" role="alert" tabindex="-1" data-galat-fokus>
                    <p style="margin: 0;">{{ session('galat_masuk') }}</p>
                    @if (str_contains(session('galat_masuk'), 'administrator') || str_contains(session('galat_masuk'), 'dinonaktifkan'))
                        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed rgba(244, 63, 94, 0.4); font-size: 13px;">
                            <a href="mailto:admin@carbontrace.id?subject=Permohonan%20Aktivasi%20Kembali%20Akun%20CarbonTrace&body=Halo%20Admin%20CarbonTrace,%20mohon%20bantuannya%20untuk%20mengaktifkan%20kembali%20akun%20saya%20dengan%20email:%20{{ old('email') }}" style="color: #fff; text-decoration: underline; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                Hubungi Administrator (admin@carbontrace.id)
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('auth.proses') }}" class="lp-auth__form" novalidate data-form-auth>
                @csrf

                <div class="ct-isian-grup">
                    <label class="ct-label" for="email">Email</label>
                    <input class="ct-isian" type="email" id="email" name="email" value="{{ old('email') }}"
                           autocomplete="email" placeholder="nama@contoh.com" required
                           @error('email') aria-invalid="true" aria-describedby="galat-email" @enderror>
                    @error('email')<p class="ct-galat" id="galat-email">{{ $message }}</p>@enderror
                </div>

                <div class="ct-isian-grup">
                    <label class="ct-label" for="password">Kata sandi</label>
                    <div class="lp-sandi">
                        <input class="ct-isian" type="password" id="password" name="password"
                               autocomplete="current-password" placeholder="Kata sandimu" required
                               @error('password') aria-invalid="true" aria-describedby="galat-password" @enderror>
                        <button type="button" class="lp-sandi__mata" aria-label="Perlihatkan kata sandi" aria-pressed="false" data-lihat-sandi="password">
                            @include('auth._ikon-mata-111')
                        </button>
                    </div>
                    @error('password')<p class="ct-galat" id="galat-password">{{ $message }}</p>@enderror
                </div>

                <div class="lp-auth__baris">
                    <label class="lp-auth__centang">
                        <input type="checkbox" name="ingat" value="1" @checked(old('ingat'))>
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                    <a href="{{ route('auth.lupa') }}" class="lp-auth__lupa">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="ct-tombol ct-tombol--utama lp-auth__kirim" data-teks-memuat="Memeriksa...">Masuk</button>
            </form>

            <p class="lp-auth__silang">Belum punya akun? <a href="{{ route('auth.daftar') }}">Daftar gratis</a></p>
        </section>
    </main>

    <p class="lp-auth__kaki">2026 CarbonTrace — Proyek Pengembangan Perangkat Lunak, Telkom University</p>
@endsection
