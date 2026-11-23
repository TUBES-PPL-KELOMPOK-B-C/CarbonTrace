@extends('layouts.aplikasi-111')

@section('judul', 'Lencana')

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

@section('isi')
    <x-notifikasi />

    {{-- Keadaan memuat: dua belas kartu kerangka, terlihat sampai halaman selesai diurai. --}}
    <div data-kerangka aria-hidden="true">
        <x-kerangka jenis="kartu" :jumlah="12" />
    </div>

    <div data-isi hidden>
        <p class="text-sm mb-4" style="color: var(--ct-text-muted);">
            Lencana terbuka begitu syaratnya terpenuhi. Yang masih terkunci tetap ditampilkan beserta syaratnya.
        </p>

        @if ($daftar->isEmpty())
            {{-- Keadaan kosong sesungguhnya: admin belum membuat satu lencana pun. Galeri pengguna
                 baru TIDAK kosong, karena seluruh lencana aktif tampil dalam keadaan terkunci. --}}
            <x-kosong pesan="Belum ada lencana"
                      subpesan="Administrator belum membuat lencana. Galerimu akan terisi begitu ada." />
        @else
            @php
                $diraih = $daftar->where('diraih', true)->count();
            @endphp

            <p class="text-sm mb-4" style="color: var(--ct-text-muted);">
                Terbuka <strong style="color: var(--ct-text);">{{ $diraih }}</strong> dari {{ $daftar->count() }} lencana.
            </p>

            <ul class="ct-lencana-kisi" role="list">
                @foreach ($daftar as $baris)
                    @php $lencana = $baris['lencana']; @endphp
                    <li class="ct-lencana @unless ($baris['diraih']) ct-lencana--terkunci @endunless">
                        <span class="ct-lencana__cakram">
                            @include('gamifikasi.manajemen-lencana-015._ikon-lencana-015', ['nama' => $lencana->icon])
                            @unless ($baris['diraih'])
                                <span class="ct-lencana__gembok" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                                    </svg>
                                </span>
                            @endunless
                        </span>

                        <h3 class="ct-lencana__nama">{{ $lencana->name }}</h3>

                        {{-- Status disebut dalam teks, bukan hanya lewat warna abu-abu. --}}
                        @if ($baris['diraih'])
                            <p class="ct-lencana__tanggal">
                                Terbuka
                                @if ($baris['earned_at'])
                                    — {{ $baris['earned_at']->locale('id')->translatedFormat('j M Y') }}
                                @endif
                            </p>
                        @else
                            <p class="ct-lencana__syarat">Terkunci — {{ $lencana->syaratTerbaca() }}</p>
                            <div class="ct-kemajuan" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                 aria-valuenow="{{ $baris['persen'] }}"
                                 aria-label="Kemajuan {{ $lencana->name }}: {{ $lencana->kemajuanTerbaca($baris['capaian']) }}">
                                <span class="ct-kemajuan__batang" style="width: {{ $baris['persen'] }}%"></span>
                            </div>
                            <p class="ct-lencana__syarat">{{ $lencana->kemajuanTerbaca($baris['capaian']) }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Keadaan berhasil: perayaan muncul sekali untuk lencana yang baru diraih. Karena baris
         badge_user hanya ditulis satu kali, pemuatan berikutnya tidak lagi memunculkannya. --}}
    @if ($baruDiraih->isNotEmpty())
        <x-modal id="modal-lencana-baru" judul="Lencana baru terbuka">
            <p class="text-sm mb-3">Syaratnya sudah kamu penuhi. Selamat.</p>
            <ul role="list" class="space-y-2">
                @foreach ($baruDiraih as $lencana)
                    <li class="flex items-center gap-3">
                        <span class="ct-lencana__cakram" style="width: 40px; height: 40px;">
                            @include('gamifikasi.manajemen-lencana-015._ikon-lencana-015', ['nama' => $lencana->icon])
                        </span>
                        <span>
                            <strong class="block text-sm">{{ $lencana->name }}</strong>
                            <span class="text-xs" style="color: var(--ct-text-muted);">{{ $lencana->syaratTerbaca() }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
            <x-slot:tombol>
                <x-tombol varian="utama"
                          onclick="const m = document.getElementById('modal-lencana-baru'); m.classList.add('hidden'); m.style.display = 'none';">
                    Tutup
                </x-tombol>
            </x-slot:tombol>
        </x-modal>
    @endif

    <script>
        // Kerangka muat diganti isi sesungguhnya begitu penanda halaman selesai diurai.
        (function () {
            var kerangka = document.querySelector('[data-kerangka]');
            var isi = document.querySelector('[data-isi]');
            if (kerangka && isi) {
                kerangka.remove();
                isi.hidden = false;
            }

            var modal = document.getElementById('modal-lencana-baru');
            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            var tombolTutup = modal.querySelector('button');
            if (tombolTutup) {
                tombolTutup.focus();
            }

            // Konfeti hanya untuk yang tidak memilih kurangi gerak.
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            var warna = ['#34d399', '#60a5fa', '#fbbf24', '#f472b6'];
            var wadah = document.createElement('div');
            wadah.className = 'ct-konfeti';
            wadah.setAttribute('aria-hidden', 'true');
            for (var i = 0; i < 40; i++) {
                var keping = document.createElement('span');
                keping.style.left = Math.random() * 100 + '%';
                keping.style.background = warna[i % warna.length];
                keping.style.animationDelay = (Math.random() * 0.8).toFixed(2) + 's';
                wadah.appendChild(keping);
            }
            document.body.appendChild(wadah);
            setTimeout(function () { wadah.remove(); }, 3600);
        })();
    </script>
@endsection
