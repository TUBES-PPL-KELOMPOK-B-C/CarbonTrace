@extends('layouts.aplikasi-111')

@section('judul', 'Pengingat')

@push('aset')
    @vite(['resources/css/target-pengingat-022.css', 'resources/js/target-pengingat-022.js'])
@endpush

@php
    $panelAwal = $errors->any() ? old('_panel', 'panel-pengingat-baru') : '';
    $jamTeks = fn ($p) => str_replace(':', '.', $p->jam());
@endphp

@section('isi')
    <x-notifikasi />

    <div class="pg" data-panel-awal="{{ $panelAwal }}">
        <p class="pg-catatan">Pengingat dikirim ke emailmu pada jam yang disetel (WIB). Pengingat dilewati kalau kamu sudah mencatat hari itu.</p>

        @if ($pengingat->isEmpty())
            <section class="ct-kartu">
                <x-kosong pesan="Belum ada pengingat. Pencatatan yang bolong membuat tren emisimu sulit dibaca.">
                    <x-slot:aksi>
                        <button type="button" class="ct-tombol ct-tombol--utama" data-buka-panel="panel-pengingat-baru">Tambah Pengingat</button>
                    </x-slot:aksi>
                </x-kosong>
            </section>
        @else
            <div class="pg-kisi">
                @foreach ($pengingat as $p)
                    <article class="ct-kartu pg-kartu {{ $p->is_active ? '' : 'pg-kartu--mati' }}" aria-labelledby="pg-jam-{{ $p->id }}">
                        <div class="pg-kartu__atas">
                            <p class="pg-kartu__jam" id="pg-jam-{{ $p->id }}">{{ $jamTeks($p) }} <span>WIB</span></p>

                            {{-- Saklar: checkbox sungguhan dengan label yang terhubung --}}
                            <form method="POST" action="{{ route('pengingat.saklar', $p) }}" class="pg-saklar">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="0">
                                <label class="ct-saklar" for="pg-saklar-{{ $p->id }}">
                                    <input type="checkbox" id="pg-saklar-{{ $p->id }}" name="is_active" value="1" @checked($p->is_active) data-saklar>
                                    <span class="ct-saklar-gelang" aria-hidden="true"></span>
                                    <span class="pg-tersembunyi">Pengingat pukul {{ $jamTeks($p) }} aktif</span>
                                </label>
                                <noscript><button type="submit" class="ct-tombol ct-tombol--hantu">Simpan</button></noscript>
                            </form>
                        </div>

                        <p class="pg-kartu__status">{{ $p->is_active ? 'Aktif setiap hari' : 'Nonaktif' }}</p>

                        <div class="pg-kartu__aksi">
                            <button type="button" class="ct-tombol ct-tombol--sekunder" data-buka-panel="panel-pengingat-{{ $p->id }}">Ubah</button>
                            <button type="button" class="ct-tombol ct-tombol--hantu pg-bahaya" data-buka-modal="modal-hapus-pengingat-{{ $p->id }}">Hapus</button>
                        </div>
                    </article>
                @endforeach

                @if ($bisaTambah)
                    <button type="button" class="ct-kartu-tambah" data-buka-panel="panel-pengingat-baru">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 5v14M5 12h14"/></svg>
                        Tambah pengingat
                    </button>
                @endif
            </div>
        @endif
    </div>

    <x-panel-geser id="panel-pengingat-baru" judul="Tambah Pengingat">
        @include('target-pengingat-dan-laporan.pengingat-pencatatan-harian-022.form', ['pengingat' => null, 'panelId' => 'panel-pengingat-baru'])
    </x-panel-geser>

    @foreach ($pengingat as $p)
        <x-panel-geser id="panel-pengingat-{{ $p->id }}" judul="Ubah Pengingat">
            @include('target-pengingat-dan-laporan.pengingat-pencatatan-harian-022.form', ['pengingat' => $p, 'panelId' => 'panel-pengingat-'.$p->id])
        </x-panel-geser>

        <x-modal id="modal-hapus-pengingat-{{ $p->id }}" judul="Hapus Pengingat" :bahaya="true">
            <p class="pg-modal__teks">Pengingat pukul {{ $jamTeks($p) }} akan dihapus.</p>
            <x-slot:tombol>
                <button type="button" class="ct-tombol ct-tombol--sekunder" data-tutup-modal="modal-hapus-pengingat-{{ $p->id }}">Kembali</button>
                <form method="POST" action="{{ route('pengingat.destroy', $p) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ct-tombol pg-tombol-bahaya">Hapus Pengingat</button>
                </form>
            </x-slot:tombol>
        </x-modal>
    @endforeach
@endsection
