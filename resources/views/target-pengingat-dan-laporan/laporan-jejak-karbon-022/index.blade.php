@extends('layouts.aplikasi-111')

@section('judul', 'Laporan')

@push('aset')
    @vite(['resources/css/target-pengingat-022.css', 'resources/js/target-pengingat-022.js'])
@endpush

@use('Illuminate\Support\Carbon')

@section('isi')
    <x-notifikasi />

    <div class="tg-kepala">
        <p class="tg-kepala__teks">Rekap emisi untuk rentang tanggal dan kategori pilihanmu, selalu dihitung dari catatan terbaru.</p>
        <a href="{{ route('laporan.create') }}" class="ct-tombol ct-tombol--utama">Susun Laporan</a>
    </div>

    @if ($laporan->isEmpty())
        <section class="ct-kartu">
            <x-kosong pesan="Belum ada laporan tersimpan. Susun laporan pertamamu untuk melihat rekap emisi dalam satu halaman.">
                <x-slot:aksi>
                    <a href="{{ route('laporan.create') }}" class="ct-tombol ct-tombol--utama">Susun Laporan</a>
                </x-slot:aksi>
            </x-kosong>
        </section>
    @else
        <div class="tg-kisi">
            @foreach ($laporan as $l)
                <article class="ct-kartu tg-riwayat">
                    <h2 class="lj-kartu__judul"><a href="{{ route('laporan.show', $l) }}">{{ $l->title }}</a></h2>
                    <p class="tg-riwayat__angka">
                        {{ Carbon::parse($l->period_start)->locale('id')->translatedFormat('j M Y') }} – {{ Carbon::parse($l->period_end)->locale('id')->translatedFormat('j M Y') }}
                    </p>
                    <p class="tg-riwayat__angka">Dibuat {{ $l->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y') }}</p>
                </article>
            @endforeach
        </div>
    @endif
@endsection
