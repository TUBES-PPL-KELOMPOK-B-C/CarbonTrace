@extends('layouts.publik-022')

@section('judul', 'Lupa Kata Sandi — CarbonTrace')
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
        <section class="lp-auth__kartu" aria-labelledby="judul-lupa">
            <span class="lp-auth__lambang" aria-hidden="true">
                <svg class="lp-lambang" focusable="false"><use href="#ct-lambang"/></svg>
            </span>
            <h1 class="lp-auth__judul" id="judul-lupa">Lupa Kata Sandi</h1>
            <p class="lp-auth__sub">Masukkan email akun Anda. Kami akan mengirimkan tautan untuk membuat kata sandi baru.</p>

            @if (session('status'))
                <div class="lp-auth__info" role="status" tabindex="-1" data-galat-fokus>
                    {{ session('status') }}
                    @if (session('tautan_lokal'))
                        <a href="{{ session('tautan_lokal') }}" class="ct-tombol ct-tombol--sekunder lp-auth__tautan-lokal">Buat Kata Sandi Baru</a>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('auth.kirim') }}" class="lp-auth__form" novalidate data-form-auth>
                @csrf

                <div class="ct-isian-grup">
                    <label class="ct-label" for="email">Email</label>
                    <input class="ct-isian" type="email" id="email" name="email" value="{{ old('email') }}"
                           autocomplete="email" placeholder="nama@contoh.com" required
                           @error('email') aria-invalid="true" aria-describedby="galat-email" @enderror>
                    @error('email')<p class="ct-galat" id="galat-email">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="ct-tombol ct-tombol--utama lp-auth__kirim" data-teks-memuat="Mengirim...">Kirim Tautan</button>
            </form>

            <p class="lp-auth__silang">Sudah ingat kata sandi Anda? <a href="{{ route('auth.masuk') }}">Masuk</a></p>
        </section>
    </main>

    <p class="lp-auth__kaki">2026 CarbonTrace — Proyek Pengembangan Perangkat Lunak, Telkom University</p>
@endsection
