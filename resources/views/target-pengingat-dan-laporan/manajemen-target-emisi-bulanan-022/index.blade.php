@extends('layouts.aplikasi-111')

@section('judul', 'Target Emisi')

@push('aset')
    @vite(['resources/css/target-pengingat-022.css', 'resources/js/target-pengingat-022.js'])
@endpush

@use('App\Services\PerhitunganEmisi111')
@use('Illuminate\Support\Carbon')

@php
    $kg = fn (float $n) => PerhitunganEmisi111::angka($n, 1);
    $labelStatus = ['aman' => 'Aman', 'hampir' => 'Hampir', 'terlampaui' => 'Terlampaui'];
    $periode = fn ($t) => Carbon::parse($t->period_start)->locale('id')->translatedFormat('j M')
        .' – '.Carbon::parse($t->period_end)->locale('id')->translatedFormat('j M Y');
    // Setelah validasi gagal, panel yang tadi dikirim dibuka lagi supaya galatnya terlihat.
    $panelAwal = $errors->any() ? old('_panel', 'panel-target-baru') : '';
    $dapatDiubah = collect([$berjalan])->filter()->merge($akanDatang->pluck(0));
@endphp

@section('isi')
    <x-notifikasi />

    <div class="tg" data-panel-awal="{{ $panelAwal }}">
        <div class="tg-kepala">
            <p class="tg-kepala__teks">Tetapkan batas emisi bulanan, lalu pantau sisa kuotanya dari catatan aktivitasmu.</p>
            <button type="button" class="ct-tombol ct-tombol--utama" data-buka-panel="panel-target-baru">Tetapkan Target</button>
        </div>

        @if ($berjalan)
            @php $r = $ringkasanBerjalan; @endphp
            <section class="ct-kartu tg-berjalan" aria-labelledby="tg-berjalan-judul">
                <div class="ct-cincin tg-cincin tg-cincin--{{ $r['status'] }}" style="--nilai: {{ min($r['persen'], 100) }}">
                    <div class="ct-cincin__isi">
                        <span class="tg-cincin__angka">{{ PerhitunganEmisi111::angka($r['persen'], 1) }}%</span>
                        <span class="tg-cincin__ket">terpakai</span>
                    </div>
                </div>

                <div class="tg-rincian">
                    <p class="tg-rincian__label">Target berjalan</p>
                    <h2 class="tg-rincian__judul" id="tg-berjalan-judul">{{ $periode($berjalan) }}</h2>
                    <x-pelat :varian="$r['status']">{{ $labelStatus[$r['status']] }}</x-pelat>

                    {{-- Padanan teks cincin, selalu terbaca --}}
                    <p class="tg-rincian__teks">
                        {{ $kg($r['terpakai']) }} dari {{ $kg($r['batas']) }} kg CO2e.
                        @if ($r['sisa'] >= 0)
                            <span class="tg-panjang">Sisa {{ $kg($r['sisa']) }} kg untuk {{ $r['sisa_hari'] }} hari.</span>
                        @else
                            <span class="tg-panjang">Terlampaui {{ $kg(abs($r['sisa'])) }} kg, masih {{ $r['sisa_hari'] }} hari lagi.</span>
                        @endif
                    </p>

                    <div class="tg-rincian__aksi">
                        <button type="button" class="ct-tombol ct-tombol--sekunder" data-buka-panel="panel-target-{{ $berjalan->id }}">Ubah Target</button>
                        <button type="button" class="ct-tombol ct-tombol--hantu tg-bahaya" data-buka-modal="modal-batal-{{ $berjalan->id }}">Batalkan</button>
                    </div>
                </div>
            </section>
        @else
            <section class="ct-kartu">
                <x-kosong pesan="Belum ada target. Tetapkan batas emisi bulananmu agar kemajuanmu bisa diukur."
                          :subpesan="$saran ? 'Saran: '.$kg($saran).' kg CO2e, sepuluh persen di bawah emisimu selama 30 hari terakhir.' : null">
                    <x-slot:aksi>
                        <button type="button" class="ct-tombol ct-tombol--utama" data-buka-panel="panel-target-baru">Tetapkan Target</button>
                    </x-slot:aksi>
                </x-kosong>
            </section>
        @endif

        @if ($akanDatang->isNotEmpty())
            <section class="tg-bagian" aria-labelledby="tg-datang-judul">
                <h2 class="tg-bagian__judul" id="tg-datang-judul">Akan datang</h2>
                <div class="tg-kisi">
                    @foreach ($akanDatang as [$t, $r])
                        <article class="ct-kartu tg-riwayat">
                            <p class="tg-riwayat__periode">{{ $periode($t) }}</p>
                            <p class="tg-riwayat__angka">Batas {{ $kg($r['batas']) }} kg CO2e</p>
                            <div class="tg-riwayat__aksi">
                                <button type="button" class="ct-tombol ct-tombol--sekunder" data-buka-panel="panel-target-{{ $t->id }}">Ubah</button>
                                <button type="button" class="ct-tombol ct-tombol--hantu tg-bahaya" data-buka-modal="modal-batal-{{ $t->id }}">Batalkan</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="tg-bagian" aria-labelledby="tg-riwayat-judul">
            <h2 class="tg-bagian__judul" id="tg-riwayat-judul">Riwayat target</h2>
            @if ($riwayat->isEmpty())
                <p class="tg-bagian__kosong">Target yang periodenya sudah berakhir akan tampil di sini.</p>
            @else
                <div class="tg-kisi">
                    @foreach ($riwayat as [$t, $r])
                        <article class="ct-kartu tg-riwayat">
                            <p class="tg-riwayat__periode">{{ $periode($t) }}</p>
                            <p class="tg-riwayat__angka">{{ $kg($r['terpakai']) }} dari {{ $kg($r['batas']) }} kg CO2e</p>
                            @if ($r['terpakai'] > $r['batas'])
                                <x-pelat varian="terlampaui">Terlampaui</x-pelat>
                            @else
                                <x-pelat varian="aman">Tercapai</x-pelat>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <x-panel-geser id="panel-target-baru" judul="Tetapkan Target">
        @include('target-pengingat-dan-laporan.manajemen-target-emisi-bulanan-022.form', ['target' => null, 'panelId' => 'panel-target-baru', 'adaBerjalan' => (bool) $berjalan])
    </x-panel-geser>

    @foreach ($dapatDiubah as $t)
        <x-panel-geser id="panel-target-{{ $t->id }}" judul="Ubah Target">
            @include('target-pengingat-dan-laporan.manajemen-target-emisi-bulanan-022.form', ['target' => $t, 'panelId' => 'panel-target-'.$t->id, 'adaBerjalan' => true])
        </x-panel-geser>

        <x-modal id="modal-batal-{{ $t->id }}" judul="Batalkan Target" :bahaya="true">
            <p class="tg-modal__teks">Target {{ $periode($t) }} akan dihapus. Catatan aktivitasmu tidak ikut terhapus.</p>
            <x-slot:tombol>
                <button type="button" class="ct-tombol ct-tombol--sekunder" data-tutup-modal="modal-batal-{{ $t->id }}">Kembali</button>
                <form method="POST" action="{{ route('target.destroy', $t) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ct-tombol tg-tombol-bahaya">Batalkan Target</button>
                </form>
            </x-slot:tombol>
        </x-modal>
    @endforeach
@endsection
