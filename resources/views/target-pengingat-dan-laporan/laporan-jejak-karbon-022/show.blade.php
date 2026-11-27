@extends('layouts.aplikasi-111')

@section('judul', $laporan->title)

@push('aset')
    @vite(['resources/css/target-pengingat-022.css', 'resources/js/target-pengingat-022.js'])
@endpush

@use('App\Services\PerhitunganEmisi111')
@use('Illuminate\Support\Carbon')

@php
    $kg = fn (float $n) => PerhitunganEmisi111::angka($n, 2, true);
    $tgl = fn (string $t) => Carbon::parse($t)->locale('id')->translatedFormat('j M Y');

    // Grafik digambar sebagai SVG di server, supaya gambar yang sama nanti bisa dipakai di PDF.
    $puncak = max(array_column($angka['tren'], 'emisi')) ?: 1;
    $jumlahTitik = count($angka['tren']);
    $titik = collect($angka['tren'])->map(fn ($t, $i) => round($jumlahTitik > 1 ? $i / ($jumlahTitik - 1) * 600 : 300, 1).','.round(150 - $t['emisi'] / $puncak * 140, 1))->join(' ');
    $hariBerisi = array_filter($angka['tren'], fn ($t) => $t['emisi'] > 0);
@endphp

@section('isi')
    <x-notifikasi />

    <div data-panel-awal="{{ $errors->any() ? 'panel-laporan' : '' }}">
        <div class="tg-kepala">
            <p class="tg-kepala__teks">
                {{ $tgl($laporan->period_start) }} – {{ $tgl($laporan->period_end) }}. Angka dihitung ulang setiap laporan dibuka, jadi ikut berubah kalau kamu mengoreksi catatan.
            </p>
            <div class="tg-rincian__aksi">
                <button type="button" class="ct-tombol ct-tombol--sekunder" data-buka-panel="panel-laporan">Ubah</button>
                <button type="button" class="ct-tombol ct-tombol--hantu tg-bahaya" data-buka-modal="modal-hapus-laporan">Hapus</button>
            </div>
        </div>

        <section class="lj-angka" aria-label="Ringkasan angka">
            <article class="ct-kartu"><h2 class="lj-angka__label">Total emisi</h2><p class="lj-total">{{ $kg($angka['total']) }} <span class="lj-total__satuan">kg CO2e</span></p></article>
            <article class="ct-kartu"><h2 class="lj-angka__label">Rata-rata per hari</h2><p class="lj-total">{{ $kg($angka['rata_harian']) }} <span class="lj-total__satuan">kg CO2e</span></p></article>
            <article class="ct-kartu"><h2 class="lj-angka__label">Catatan</h2><p class="lj-total">{{ $angka['jumlah_catatan'] }} <span class="lj-total__satuan">dalam {{ $angka['jumlah_hari'] }} hari</span></p></article>
        </section>

        @if ($angka['jumlah_catatan'] === 0)
            <section class="ct-kartu"><p class="lj-catatan">Tidak ada catatan pada rentang ini.</p></section>
        @else
            <section class="ct-kartu tg-bagian" aria-labelledby="lj-tren-judul">
                <h2 class="ct-kartu__judul" id="lj-tren-judul">Tren harian</h2>
                <svg class="lj-grafik" viewBox="0 0 600 160" preserveAspectRatio="none" aria-hidden="true" focusable="false"><polyline points="{{ $titik }}"/></svg>
                <table class="ct-tabel-tersembunyi">
                    <caption>Emisi per hari, hanya hari yang memiliki catatan</caption>
                    <thead><tr><th scope="col">Tanggal</th><th scope="col">Emisi (kg CO2e)</th></tr></thead>
                    <tbody>
                        @foreach ($hariBerisi as $t)
                            <tr><td>{{ $tgl($t['tanggal']) }}</td><td>{{ $kg($t['emisi']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if ($angka['per_kategori'])
            <section class="ct-kartu tg-bagian" aria-labelledby="lj-kategori-judul">
                <h2 class="ct-kartu__judul" id="lj-kategori-judul">Rincian per kategori</h2>
                <table class="ct-tabel">
                    <thead><tr><th scope="col">Kategori</th><th scope="col" class="angka">Emisi (kg)</th><th scope="col" class="angka">Porsi</th></tr></thead>
                    <tbody>
                        @foreach ($angka['per_kategori'] as $k)
                            <tr>
                                <td data-label="Kategori"><span class="lj-warna" style="--warna: {{ $k['warna'] }}" aria-hidden="true"></span>{{ $k['nama'] }}</td>
                                <td data-label="Emisi (kg)" class="angka">{{ $kg($k['emisi']) }}</td>
                                <td data-label="Porsi" class="angka">{{ PerhitunganEmisi111::angka($k['persen'], 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif
    </div>

    <x-panel-geser id="panel-laporan" judul="Ubah Laporan">
        @include('target-pengingat-dan-laporan.laporan-jejak-karbon-022.form')
    </x-panel-geser>

    <x-modal id="modal-hapus-laporan" judul="Hapus Laporan" :bahaya="true">
        <p class="tg-modal__teks">Laporan "{{ $laporan->title }}" akan dihapus. Catatan aktivitasmu tidak ikut terhapus.</p>
        <x-slot:tombol>
            <button type="button" class="ct-tombol ct-tombol--sekunder" data-tutup-modal="modal-hapus-laporan">Kembali</button>
            <form method="POST" action="{{ route('laporan.destroy', $laporan) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="ct-tombol tg-tombol-bahaya">Hapus Laporan</button>
            </form>
        </x-slot:tombol>
    </x-modal>
@endsection
