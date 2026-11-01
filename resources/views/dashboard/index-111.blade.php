@extends('layouts.aplikasi-111')

@section('judul', 'Dashboard')

@push('aset')
    @vite(['resources/css/dashboard-111.css'])
@endpush

@use('App\Services\PerhitunganEmisi111')

@php
    $kg = fn (float $n) => PerhitunganEmisi111::angka($n, 2, true);
    $gagal = fn ($bagian) => is_array($bagian) && isset($bagian['gagal']);

    // Tujuan tombol "Catat Aktivitas" dan "Tetapkan Target": aktif begitu halaman pemiliknya ada.
    $ruteCatat = Route::has('aktivitas.create') ? route('aktivitas.create') : null;
    $ruteTarget = Route::has('target.index') ? route('target.index') : null;

    ['emisi' => $emisi, 'target' => $target, 'poin' => $poin, 'terbaru' => $terbaru, 'pengumuman' => $pengumuman] = $ringkasan;
@endphp

@section('isi')
    <p class="ds-sapa">Halo, {{ \Illuminate\Support\Str::before($pengguna->name, ' ') }}. Ini ringkasan jejak karbonmu.</p>

    {{-- Pengumuman terbaru yang sedang berlaku (PBI-18) --}}
    @if (is_object($pengumuman))
        <aside class="ds-umum" aria-label="Pengumuman">
            <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 10v4h3l6 4V6L7 10zM17 9a4 4 0 0 1 0 6"/></svg>
            <div>
                <p class="ds-umum__judul">{{ $pengumuman->title }}</p>
                @if ($pengumuman->content)
                    <p class="ds-umum__isi">{{ \Illuminate\Support\Str::limit(strip_tags($pengumuman->content), 160) }}</p>
                @endif
            </div>
        </aside>
    @endif

    {{-- Baris 1: empat kartu statistik --}}
    <section class="ds-statistik" aria-label="Ringkasan angka">
        @include('dashboard._kartu-statistik-111', [
            'label' => 'Emisi hari ini',
            'gagal' => $gagal($emisi),
            'angka' => $kg($emisi['hariIni'] ?? 0),
            'satuan' => 'kg CO2e',
        ])
        @include('dashboard._kartu-statistik-111', [
            'label' => 'Emisi bulan ini',
            'gagal' => $gagal($emisi),
            'angka' => $kg($emisi['bulanIni'] ?? 0),
            'satuan' => 'kg CO2e',
        ])

        @if ($gagal($target) || $target)
            @include('dashboard._kartu-statistik-111', [
                'label' => 'Sisa kuota target',
                'gagal' => $gagal($target),
                'angka' => $kg(abs($target['sisa'] ?? 0)),
                'satuan' => 'kg CO2e',
                'keterangan' => ($target['sisa'] ?? 0) < 0
                    ? 'Terlampaui dari batas '.$kg($target['batas'] ?? 0).' kg'
                    : 'Tersisa dari batas '.$kg($target['batas'] ?? 0).' kg',
            ])
        @else
            {{-- Tanpa target berjalan: tombol, bukan angka nol yang membingungkan --}}
            <article class="ds-stat ds-stat--ajak">
                <h2 class="ds-stat__label">Sisa kuota target</h2>
                <p class="ds-stat__ket">Belum ada target untuk periode ini.</p>
                @if ($ruteTarget)
                    <a href="{{ $ruteTarget }}" class="ct-tombol ct-tombol--sekunder ds-stat__tombol">Tetapkan Target</a>
                @else
                    <span class="ct-tombol ct-tombol--sekunder ds-stat__tombol" aria-disabled="true">Tetapkan Target</span>
                @endif
            </article>
        @endif

        @include('dashboard._kartu-statistik-111', [
            'label' => 'EcoPoint',
            'gagal' => $gagal($poin),
            'angka' => number_format(is_int($poin) ? $poin : 0, 0, ',', '.'),
            'satuan' => 'poin',
        ])
    </section>

    {{-- Pengguna hari pertama: ajakan besar, bukan grafik kosong tanpa penjelasan --}}
    @unless ($ringkasan['adaCatatan'])
        <section class="ds-ajak" aria-labelledby="ds-ajak-judul">
            <svg class="ds-ajak__gambar" viewBox="0 0 160 64" aria-hidden="true" focusable="false">
                <polyline points="4,52 34,40 58,46 86,24 112,32 156,10"/>
                <circle cx="156" cy="10" r="4"/>
            </svg>
            <h2 class="ds-ajak__judul" id="ds-ajak-judul">Catat aktivitas pertamamu untuk melihat angkanya.</h2>
            <p class="ds-ajak__teks">Setiap perjalanan, pemakaian listrik, makanan, dan sampah yang kamu catat akan dihitung emisinya secara otomatis.</p>
            @if ($ruteCatat)
                <a href="{{ $ruteCatat }}" class="ct-tombol ct-tombol--utama">Catat Aktivitas</a>
            @else
                <span class="ct-tombol ct-tombol--utama" aria-disabled="true" title="Halaman pencatatan segera hadir">Catat Aktivitas</span>
            @endif
        </section>
    @endunless

    {{-- Baris 2: grafik tren (KF-22) dan rincian kategori (KF-23) --}}
    {{-- TODO: ganti isi dua kartu ini dengan grafik KF-22 dan KF-23 setelah keduanya dibuat --}}
    <section class="ds-grafik" aria-label="Grafik">
        @foreach (['Tren emisi 7 hari' => 'tren harianmu', 'Rincian per kategori' => 'porsi tiap kategori'] as $judul => $isi)
            <article class="ds-kartu">
                <h2 class="ds-kartu__judul">{{ $judul }}</h2>
                <div class="ds-kosong">
                    <svg class="ds-kosong__gambar" viewBox="0 0 160 64" aria-hidden="true" focusable="false">
                        <polyline points="4,52 34,40 58,46 86,24 112,32 156,10"/>
                    </svg>
                    <p>
                        {{ $ringkasan['adaCatatan']
                            ? 'Grafik '.$isi.' sedang disiapkan.'
                            : 'Grafik '.$isi.' muncul setelah kamu mencatat aktivitas.' }}
                    </p>
                </div>
            </article>
        @endforeach
    </section>

    {{-- Baris 3: lima aktivitas terbaru --}}
    <section class="ds-kartu ds-terbaru" aria-labelledby="ds-terbaru-judul">
        <h2 class="ds-kartu__judul" id="ds-terbaru-judul">Aktivitas terbaru</h2>

        @if ($gagal($terbaru))
            <p class="ds-gagal">Gagal memuat. <a href="{{ route('dashboard.index') }}">Coba lagi</a></p>
        @elseif ($terbaru->isEmpty())
            <div class="ds-kosong">
                <p>Belum ada aktivitas. Catatanmu yang terbaru akan tampil di sini.</p>
            </div>
        @else
            <ul class="ds-daftar">
                @foreach ($terbaru as $a)
                    <li class="ds-daftar__butir">
                        <div>
                            <p class="ds-daftar__nama">{{ $a->nama ?? 'Aktivitas' }}</p>
                            <p class="ds-daftar__ket">
                                {{ \Illuminate\Support\Carbon::parse($a->activity_date)->locale('id')->translatedFormat('j M Y') }}
                                @isset($a->kategori) · {{ $a->kategori }} @endisset
                                · {{ PerhitunganEmisi111::angka($a->amount) }} {{ $a->satuan ?? '' }}
                            </p>
                        </div>
                        <p class="ds-daftar__emisi">{{ $kg((float) $a->emission) }} <span>kg CO2e</span></p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Tombol pintas pencatatan (PBI-04) --}}
    @if ($ruteCatat)
        <a href="{{ $ruteCatat }}" class="ds-tambah" aria-label="Catat aktivitas">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 5v14M5 12h14"/></svg>
        </a>
    @else
        <span class="ds-tambah" aria-disabled="true" role="img" aria-label="Catat aktivitas (segera hadir)" title="Halaman pencatatan segera hadir">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 5v14M5 12h14"/></svg>
        </span>
    @endif
@endsection
