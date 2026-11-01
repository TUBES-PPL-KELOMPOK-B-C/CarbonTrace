@extends('layouts.publik-022')

@section('judul', 'Kalkulator Jejak Karbon — CarbonTrace')
@section('deskripsi', 'Hitung jejak karbon satu aktivitasmu tanpa perlu membuat akun.')
@section('kelas-body', 'lp-kal-halaman')

@push('aset')
    @vite(['resources/css/kalkulator-111.css', 'resources/js/kalkulator-111.js'])
@endpush

@php
    // Ikon per kategori, dicocokkan dari namanya. Kategori lain memakai lambang CarbonTrace.
    $ikon = [
        'listrik' => '<path d="M13 2 4 14h7l-1 8 9-12h-7Z"/>',
        'transport' => '<path d="M5 17h14M6 17l1.5-6h9L18 17"/><circle cx="8" cy="18" r="2"/><circle cx="16" cy="18" r="2"/><path d="M9 11V7h6v4"/>',
        'makan' => '<path d="M4 11h16a8 8 0 0 1-16 0Z"/><path d="M12 3v4M8 5v2M16 5v2"/>',
        'sampah' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/><path d="M10 11v6M14 11v6"/>',
    ];
    $ikonUntuk = function (string $nama) use ($ikon): ?string {
        foreach ($ikon as $kunci => $jalur) {
            if (str_contains(strtolower($nama), $kunci)) {
                return $jalur;
            }
        }

        return null;
    };

    $faktorLama = (int) old('emission_factor_id');
    $kategoriLama = (int) old('kategori', $kategori->first(fn ($k) => $k->faktor->contains('id', $faktorLama))?->id);
@endphp

@section('isi')
    @include('publik.beranda._latar-022')
    @include('publik.beranda._navbar-022')

    <main class="lp-bagian lp-kal">
        <div class="lp-kal__wadah">
            <header class="lp-kepala lp-kepala--tengah lp-kal__kepala">
                <p class="lp-label">KALKULATOR PUBLIK</p>
                <h1 class="lp-judul-bagian">Kalkulator Jejak Karbon</h1>
                <p class="lp-kal__sub">Pilih aktivitas, isi jumlahnya, lalu lihat emisinya. Tanpa akun, dan tidak ada data yang disimpan.</p>
            </header>

            @if ($kategori->isEmpty())
                {{-- Tabel faktor emisi (PBI Renaya) belum ada atau masih kosong --}}
                <section class="ct-kartu lp-kal__kosong" role="status">
                    <svg class="lp-lambang" aria-hidden="true" focusable="false"><use href="#ct-lambang"/></svg>
                    <h2 class="ct-kartu__judul">Kalkulator belum siap</h2>
                    <p>Data faktor emisi belum diisi administrator, jadi belum ada aktivitas yang bisa dihitung. Coba lagi nanti.</p>
                    <a href="{{ route('beranda') }}" class="ct-tombol ct-tombol--sekunder">Kembali ke Beranda</a>
                </section>
            @else
                <form method="POST" action="{{ route('kalkulator.hitung') }}" class="ct-kartu lp-kal__form" novalidate
                      data-kalkulator data-api="{{ route('kalkulator.api') }}">
                    @csrf

                    @if (session('galat_kalkulator'))
                        <div class="lp-kal__galat" role="alert">{{ session('galat_kalkulator') }}</div>
                    @endif
                    <div class="lp-kal__galat" role="alert" data-galat-umum hidden></div>

                    <fieldset class="lp-kal__langkah">
                        <legend class="ct-label">1. Pilih kategori</legend>
                        <div class="ct-pilihan-kartu">
                            @foreach ($kategori as $k)
                                <label class="ct-pilihan-kartu__butir">
                                    <input type="radio" name="kategori" value="{{ $k->id }}" @checked($kategoriLama === $k->id) data-pilih-kategori>
                                    <span class="ct-pilihan-kartu__isi">
                                        @if ($jalur = $ikonUntuk($k->nama))
                                            <svg class="ct-pilihan-kartu__ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $jalur !!}</svg>
                                        @else
                                            <svg class="ct-pilihan-kartu__ikon" aria-hidden="true" focusable="false"><use href="#ct-lambang"/></svg>
                                        @endif
                                        {{ $k->nama }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="ct-isian-grup">
                        <label class="ct-label" for="emission_factor_id">2. Pilih aktivitas</label>
                        <select id="emission_factor_id" name="emission_factor_id" class="ct-isian lp-kal__pilih" required
                                @error('emission_factor_id') aria-invalid="true" aria-describedby="galat-faktor" @enderror>
                            <option value="">Pilih aktivitas…</option>
                            @foreach ($kategori as $k)
                                <optgroup label="{{ $k->nama }}" data-kategori="{{ $k->id }}">
                                    @foreach ($k->faktor as $f)
                                        <option value="{{ $f->id }}" data-satuan="{{ $f->unit }}" @selected($faktorLama === $f->id)>
                                            {{ $f->name }} ({{ $f->unit }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <p class="ct-galat" id="galat-faktor" data-galat-untuk="emission_factor_id" @if (! $errors->has('emission_factor_id')) hidden @endif>{{ $errors->first('emission_factor_id') }}</p>
                    </div>

                    <div class="ct-isian-grup">
                        <label class="ct-label" for="amount">3. Jumlah</label>
                        <div class="lp-kal__jumlah">
                            <input id="amount" name="amount" type="text" inputmode="decimal" class="ct-isian" required
                                   value="{{ old('amount') }}" placeholder="Contoh: 4 atau 1,5" autocomplete="off"
                                   @error('amount') aria-invalid="true" @enderror aria-describedby="satuan-jumlah galat-jumlah">
                            <span class="lp-kal__satuan" id="satuan-jumlah" data-satuan-jumlah>satuan</span>
                        </div>
                        <p class="ct-galat" id="galat-jumlah" data-galat-untuk="amount" @if (! $errors->has('amount')) hidden @endif>{{ $errors->first('amount') }}</p>
                    </div>

                    <button type="submit" class="ct-tombol ct-tombol--utama lp-kal__kirim" data-teks-memuat="Menghitung...">Hitung Emisi</button>
                </form>

                <section class="lp-kal__hasil" id="hasil" aria-labelledby="judul-hasil" aria-live="polite" data-hasil>
                    <h2 class="lp-kal__hasil-judul" id="judul-hasil">Hasil</h2>

                    <div class="ct-kartu-hasil ct-kartu-hasil--kosong" data-hasil-kosong @if ($hasil) hidden @endif>
                        <p>Hasilmu akan muncul di sini.</p>
                    </div>

                    <div class="ct-kartu-hasil" data-hasil-isi @unless ($hasil) hidden @endunless>
                        <p class="ct-kartu-hasil__angka"><span data-hasil-angka>{{ $hasil['emisi_teks'] ?? '' }}</span></p>
                        <p class="ct-kartu-hasil__banding" data-hasil-banding>{{ $hasil['perbandingan'] ?? '' }}</p>
                        <p class="ct-kartu-hasil__rincian">Cara hitung: <span data-hasil-rincian>{{ $hasil['rincian'] ?? '' }}</span></p>

                        <div class="ct-kartu-hasil__aksi">
                            @guest
                                <a href="{{ route('auth.daftar') }}" class="ct-tombol ct-tombol--utama">Daftar Gratis</a>
                            @endguest
                            <a href="{{ route('kalkulator.index') }}" class="ct-tombol ct-tombol--sekunder" data-hitung-lagi>Hitung Lagi</a>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </main>

    @include('publik.beranda._footer-022')
@endsection
