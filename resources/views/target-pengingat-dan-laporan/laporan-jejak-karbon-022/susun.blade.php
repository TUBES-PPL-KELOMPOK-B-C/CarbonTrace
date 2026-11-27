@extends('layouts.aplikasi-111')

@section('judul', 'Susun Laporan')

@push('aset')
    @vite(['resources/css/target-pengingat-022.css', 'resources/js/target-pengingat-022.js'])
@endpush

@section('isi')
    <x-notifikasi />

    <div class="lj-susun">
        <section class="ct-kartu">
            @include('target-pengingat-dan-laporan.laporan-jejak-karbon-022.form', ['laporan' => null])
        </section>

        {{-- Diisi JavaScript dari /api/laporan/pratinjau; bisa dilipat di layar sempit --}}
        <details class="ct-kartu lj-pratinjau" open data-pratinjau>
            <summary class="ct-kartu__judul">Pratinjau</summary>

            <div class="lj-kerangka" data-keadaan="memuat" hidden aria-label="Memuat pratinjau">
                <span class="ct-kerangka"></span><span class="ct-kerangka lj-kerangka__grafik"></span><span class="ct-kerangka"></span><span class="ct-kerangka"></span>
            </div>

            <p class="tg-gagal" data-keadaan="galat" hidden>
                <span data-pesan>Pratinjau gagal dimuat.</span>
                <button type="button" class="ct-tombol ct-tombol--hantu" data-coba-lagi>Coba lagi</button>
            </p>

            <div data-keadaan="isi" hidden aria-live="polite">
                <p class="lj-total"><span data-total></span> <span class="lj-total__satuan">kg CO2e</span></p>
                <p class="lj-catatan"><span data-rata></span> kg per hari, dari <span data-jumlah></span> catatan. Perkiraan <span data-halaman></span> halaman.</p>
                <p class="lj-catatan" data-tanpa-catatan hidden>Tidak ada catatan pada rentang ini.</p>
                <div class="lj-rinci">
                    <svg class="lj-grafik" viewBox="0 0 600 160" preserveAspectRatio="none" aria-hidden="true" focusable="false"><polyline data-garis points=""/></svg>
                    <table class="ct-tabel">
                        <thead><tr><th scope="col">Kategori</th><th scope="col" class="angka">Emisi (kg)</th><th scope="col" class="angka">Porsi</th></tr></thead>
                        <tbody data-baris></tbody>
                    </table>
                </div>
            </div>

            <noscript><p class="lj-catatan">Pratinjau memerlukan JavaScript. Laporan tetap bisa disimpan dan dibuka.</p></noscript>
        </details>
    </div>
@endsection
