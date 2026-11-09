@extends('layouts.publik-022')

@section('judul', 'Buat Kata Sandi Baru — CarbonTrace')
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
        <section class="lp-auth__kartu" aria-labelledby="judul-atur">
            <span class="lp-auth__lambang" aria-hidden="true">
                <svg class="lp-lambang" focusable="false"><use href="#ct-lambang"/></svg>
            </span>
            <h1 class="lp-auth__judul" id="judul-atur">Buat Kata Sandi Baru</h1>
            <p class="lp-auth__sub">Gunakan kata sandi yang belum pernah Anda gunakan di layanan lain.</p>

            @if (session('galat_atur'))
                <div class="lp-auth__galat" role="alert" tabindex="-1" data-galat-fokus>
                    {{ session('galat_atur') }} <a href="{{ route('auth.lupa') }}">Kirim ulang tautan</a>
                </div>
            @endif

            <form method="POST" action="{{ route('auth.atur') }}" class="lp-auth__form" novalidate data-form-auth>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="ct-isian-grup">
                    <label class="ct-label" for="email">Email</label>
                    <input class="ct-isian" type="email" id="email" name="email" value="{{ old('email', $email) }}"
                           autocomplete="email" placeholder="nama@contoh.com" required
                           @error('email') aria-invalid="true" aria-describedby="galat-email" @enderror>
                    @error('email')<p class="ct-galat" id="galat-email">{{ $message }}</p>@enderror
                </div>

                <div class="ct-isian-grup">
                    <label class="ct-label" for="password">Kata sandi baru</label>
                    <div class="lp-sandi">
                        <input class="ct-isian" type="password" id="password" name="password"
                               autocomplete="new-password" minlength="8" placeholder="Minimal 8 karakter" required
                               aria-describedby="kekuatan-sandi @error('password') galat-password @enderror"
                               @error('password') aria-invalid="true" @enderror data-kekuatan-sumber>
                        <button type="button" class="lp-sandi__mata" aria-label="Perlihatkan kata sandi" aria-pressed="false" data-lihat-sandi="password">
                            @include('auth._ikon-mata-111')
                        </button>
                    </div>
                    <div class="ct-kekuatan-sandi" id="kekuatan-sandi" data-kekuatan="0" aria-live="polite">
                        <span class="ct-kekuatan-sandi__batang" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                        <span class="ct-kekuatan-sandi__teks" data-kekuatan-teks>Belum diisi</span>
                    </div>
                    @error('password')<p class="ct-galat" id="galat-password">{{ $message }}</p>@enderror
                </div>

                <div class="ct-isian-grup">
                    <label class="ct-label" for="password_confirmation">Konfirmasi kata sandi baru</label>
                    <div class="lp-sandi">
                        <input class="ct-isian" type="password" id="password_confirmation" name="password_confirmation"
                               autocomplete="new-password" placeholder="Ulangi kata sandi baru" required>
                        <button type="button" class="lp-sandi__mata" aria-label="Perlihatkan kata sandi" aria-pressed="false" data-lihat-sandi="password_confirmation">
                            @include('auth._ikon-mata-111')
                        </button>
                    </div>
                </div>

                <button type="submit" class="ct-tombol ct-tombol--utama lp-auth__kirim" data-teks-memuat="Menyimpan...">Simpan Kata Sandi</button>
            </form>

            <p class="lp-auth__silang">Batal? <a href="{{ route('auth.masuk') }}">Kembali ke halaman masuk</a></p>
        </section>
    </main>

    <p class="lp-auth__kaki">2026 CarbonTrace — Proyek Pengembangan Perangkat Lunak, Telkom University</p>
@endsection
