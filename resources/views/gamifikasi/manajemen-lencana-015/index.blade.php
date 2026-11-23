@extends('layouts.aplikasi-111')

@section('judul', 'Kelola Lencana')

@push('aset')
    @vite(['resources/css/gamifikasi-015.css'])
    {{-- Tanpa JavaScript, kerangka muat tidak pernah diganti, jadi langsung tampilkan isinya. --}}
    <noscript>
        <style>
            [data-kerangka] { display: none !important; }
            [data-isi][hidden] { display: block !important; }
        </style>
    </noscript>
@endpush

@php
    // Keadaan galat: setelah validasi gagal, modal yang tadi dikirim dibuka lagi supaya pesan
    // galatnya terlihat, bukan tersembunyi di balik modal yang sudah tertutup.
    $modalGalat = $errors->any() ? old('_modal') : null;
@endphp

@section('isi')
    <x-notifikasi />

    {{-- Keadaan memuat: kartu kerangka, terlihat sampai halaman selesai diurai. --}}
    <div data-kerangka aria-hidden="true">
        <x-kerangka jenis="kartu" :jumlah="6" />
    </div>

    <div data-isi hidden>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <p class="text-sm m-0" style="color: var(--ct-text-muted); max-width: 52ch;">
                Lencana diberikan otomatis begitu syaratnya terpenuhi. Menurunkan syarat membuat
                pengguna yang tadinya belum memenuhi langsung meraihnya; menaikkan syarat tidak
                mencabut lencana yang sudah diraih.
            </p>
            <x-tombol varian="utama" data-buka-modal="modal-lencana-tambah">Tambah Lencana</x-tombol>
        </div>

        @if ($daftar->isEmpty())
            <x-kosong pesan="Belum ada lencana"
                      subpesan="Tambah lencana pertama, lalu lencana itu langsung tampil di galeri semua pengguna.">
                <x-slot:aksi>
                    <x-tombol varian="utama" data-buka-modal="modal-lencana-tambah">Tambah Lencana</x-tombol>
                </x-slot:aksi>
            </x-kosong>
        @else
            <ul class="ct-lencana-kisi ct-lencana-kisi--admin" role="list">
                @foreach ($daftar as $lencana)
                    <li class="ct-lencana @unless ($lencana->is_active) ct-lencana--nonaktif @endunless">
                        <span class="ct-lencana__cakram">
                            @include('gamifikasi.manajemen-lencana-015._ikon-lencana-015', ['nama' => $lencana->icon])
                        </span>

                        <h3 class="ct-lencana__nama">{{ $lencana->name }}</h3>
                        <p class="ct-lencana__syarat">{{ $lencana->syaratTerbaca() }}</p>

                        @if ($lencana->description)
                            <p class="ct-lencana__syarat">{{ $lencana->description }}</p>
                        @endif

                        <x-pelat :varian="$lencana->is_active ? 'aman' : 'netral'">
                            {{ $lencana->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-pelat>

                        <p class="ct-lencana__syarat">Sudah diraih {{ $lencana->jumlah_peraih }} pengguna</p>

                        <div class="ct-lencana__aksi">
                            <x-tombol varian="sekunder" data-buka-modal="modal-lencana-ubah-{{ $lencana->id }}">
                                Ubah syarat
                            </x-tombol>

                            <form method="POST" action="{{ route('lencana.status', $lencana) }}">
                                @csrf
                                @method('PATCH')
                                <x-tombol varian="hantu" tipe="submit">
                                    {{ $lencana->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </x-tombol>
                            </form>

                            {{-- Lencana yang sudah diraih tidak bisa dihapus permanen. Tombolnya tetap
                                 ada supaya admin melihat alasannya, bukan hilang tanpa penjelasan. --}}
                            <form method="POST" action="{{ route('lencana.destroy', $lencana) }}"
                                  data-konfirmasi="Hapus lencana {{ $lencana->name }} secara permanen?">
                                @csrf
                                @method('DELETE')
                                <x-tombol varian="bahaya" tipe="submit"
                                          title="{{ $lencana->jumlah_peraih > 0
                                              ? 'Sudah diraih '.$lencana->jumlah_peraih.' pengguna — nonaktifkan saja'
                                              : 'Hapus lencana ini' }}">
                                    Hapus
                                </x-tombol>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Modal tambah --}}
    <x-modal id="modal-lencana-tambah" judul="Tambah lencana">
        <form method="POST" action="{{ route('lencana.store') }}" id="form-lencana-tambah">
            @csrf
            @include('gamifikasi.manajemen-lencana-015.form', [
                'lencana' => null,
                'jenis' => $jenis,
                'modalId' => 'modal-lencana-tambah',
            ])
        </form>
        <x-slot:tombol>
            <x-tombol varian="hantu" data-tutup-modal="modal-lencana-tambah">Batal</x-tombol>
            <x-tombol varian="utama" tipe="submit" form="form-lencana-tambah">Simpan</x-tombol>
        </x-slot:tombol>
    </x-modal>

    {{-- Modal ubah, satu per lencana --}}
    @foreach ($daftar as $lencana)
        <x-modal id="modal-lencana-ubah-{{ $lencana->id }}" judul="Ubah {{ $lencana->name }}">
            <form method="POST" action="{{ route('lencana.update', $lencana) }}"
                  id="form-lencana-ubah-{{ $lencana->id }}">
                @csrf
                @method('PUT')
                @include('gamifikasi.manajemen-lencana-015.form', [
                    'lencana' => $lencana,
                    'jenis' => $jenis,
                    'modalId' => 'modal-lencana-ubah-'.$lencana->id,
                ])
            </form>
            <x-slot:tombol>
                <x-tombol varian="hantu" data-tutup-modal="modal-lencana-ubah-{{ $lencana->id }}">Batal</x-tombol>
                <x-tombol varian="utama" tipe="submit" form="form-lencana-ubah-{{ $lencana->id }}">Simpan</x-tombol>
            </x-slot:tombol>
        </x-modal>
    @endforeach

    <script>
        (function () {
            // Kerangka muat diganti isi sesungguhnya begitu halaman selesai diurai.
            var kerangka = document.querySelector('[data-kerangka]');
            var isi = document.querySelector('[data-isi]');
            if (kerangka && isi) {
                kerangka.remove();
                isi.hidden = false;
            }

            function buka(id) {
                var m = document.getElementById(id);
                if (!m) { return; }
                m.classList.remove('hidden');
                m.style.display = 'flex';
                var pertama = m.querySelector('input, select, textarea, button');
                if (pertama) { pertama.focus(); }
            }

            function tutup(id) {
                var m = document.getElementById(id);
                if (!m) { return; }
                m.classList.add('hidden');
                m.style.display = 'none';
            }

            document.querySelectorAll('[data-buka-modal]').forEach(function (tombol) {
                tombol.addEventListener('click', function () { buka(tombol.dataset.bukaModal); });
            });

            document.querySelectorAll('[data-tutup-modal]').forEach(function (tombol) {
                tombol.addEventListener('click', function () { tutup(tombol.dataset.tutupModal); });
            });

            // Esc menutup modal yang sedang terbuka, supaya alurnya bisa diselesaikan dengan papan ketik.
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') { return; }
                document.querySelectorAll('.ct-latar-belakang:not(.hidden)').forEach(function (m) {
                    tutup(m.id);
                });
            });

            document.querySelectorAll('form[data-konfirmasi]').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (!window.confirm(form.dataset.konfirmasi)) { e.preventDefault(); }
                });
            });

            var modalGalat = @json($modalGalat);
            if (modalGalat) { buka(modalGalat); }
        })();
    </script>
@endsection
