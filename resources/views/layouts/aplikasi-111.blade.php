@php
    // Kerangka halaman setelah masuk: sidebar kiri, bilah atas, isi di kanan; di layar sempit
    // sidebar menjadi menu bawah lima ikon. Menu yang punya subfitur tampil sebagai kelompok yang
    // bisa dibuka-tutup (drill-down) dan otomatis terbuka bila salah satu isinya sedang dibuka.
    // Menu yang halamannya belum dibuat pemiliknya tampil redup dan aktif begitu rutenya terdaftar.
    //
    // Dipakai juga oleh halaman yang ditulis untuk layouts.app-221: bagian 'content', 'title',
    // 'page_title', serta stack 'styles' dan 'scripts' ikut dikenali.
    $ikon = [
        'dashboard' => '<path d="M4 13h6V4H4zM14 20h6v-9h-6zM14 4v4h6V4zM4 20h6v-4H4z"/>',
        'aktivitas' => '<circle cx="12" cy="12" r="8"/><path d="M12 8v8M8 12h8"/>',
        'catat' => '<path d="M12 5v14M5 12h14"/>',
        'riwayat' => '<path d="M4 12a8 8 0 1 0 2.3-5.7L4 8.6"/><path d="M4 4v4.6h4.6M12 8v4l3 2"/>',
        'template' => '<path d="M4 5h16v3H4zM4 12h7v7H4zM15 12h5v7h-5z"/>',
        'target' => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r=".8"/>',
        'pengingat' => '<circle cx="12" cy="13" r="7"/><path d="M12 9v4l2 2M5 4 2 7M19 4l3 3"/>',
        'laporan' => '<path d="M6 3h9l4 4v14H6zM15 3v4h4M9 12h7M9 16h7"/>',
        'tantangan' => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'peringkat' => '<path d="M8 21V11H4v10zM14 21V5h-4v16zM20 21v-7h-4v7z"/>',
        'edukasi' => '<path d="M4 5h6a2 2 0 0 1 2 2v13a2 2 0 0 0-2-2H4zM20 5h-6a2 2 0 0 0-2 2v13a2 2 0 0 1 2-2h6z"/>',
        'profil' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
        'rumah' => '<path d="M3 10l9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M9 21v-7h6v7"/>',
        'kuis' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .8-1 1.5V14M12 17h.01"/>',
        'pengguna' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c1-3.5 3.5-5.5 6.5-5.5s5.5 2 6.5 5.5M16 4.5a3.5 3.5 0 0 1 0 7M18 14.8c1.8.8 3 2.5 3.5 5.2"/>',
    ];

    // [label, rute, ikon, pola rute aktif, subfitur]
    $menu = [
        ['Dashboard', 'dashboard.index', 'dashboard', 'dashboard*', []],
        ['Aktivitas', null, 'aktivitas', null, [
            ['Catat Aktivitas', 'aktivitas.create', 'catat', 'aktivitas.create'],
            ['Riwayat', 'aktivitas.index', 'riwayat', 'aktivitas.index|aktivitas.edit'],
            ['Template Rutin', 'template.index', 'template', 'template.*'],
        ]],
        ['Target', null, 'target', null, [
            ['Target Emisi', 'target.index', 'target', 'target.*'],
            ['Pengingat', 'pengingat.index', 'pengingat', 'pengingat.*'],
            ['Laporan', 'laporan.index', 'laporan', 'laporan.*'],
        ]],
        ['Tantangan', 'ikut.daftar', 'tantangan', 'ikut.*', []],
        ['Papan Peringkat', 'peringkat.index', 'peringkat', 'peringkat.*', []],
        ['Edukasi', 'artikel.publik', 'edukasi', 'artikel.publik|artikel.baca', []],
        ['Profil', null, 'profil', null, [
            ['Profil Saya', 'profil.index', 'profil', 'profil.*'],
            ['Rumah Tangga', 'rumah.index', 'rumah', 'rumah.*'],
        ]],
    ];

    $pengguna = auth()->user();
    // Modul 2 (Hadi) menamai perannya "admin", modul Akun (Rosi) "administrator"; keduanya dikenali.
    $adminMenu = method_exists($pengguna, 'hasAnyRole') && $pengguna->hasAnyRole(['administrator', 'admin'])
        ? [
            ['Manajemen Pengguna', 'pengguna.index', 'pengguna', 'pengguna.*'],
            ['Bank Soal Kuis', 'kuis.index', 'kuis', 'kuis.*'],
        ]
        : [];

    $aktif = fn (?string $pola) => $pola && request()->routeIs(...explode('|', $pola));

    // Menu bawah layar sempit: lima menu utama
    $menuBawah = [
        ['Dashboard', 'dashboard.index', 'dashboard', 'dashboard*'],
        ['Catat', 'aktivitas.create', 'catat', 'aktivitas.create'],
        ['Riwayat', 'aktivitas.index', 'riwayat', 'aktivitas.index|aktivitas.edit'],
        ['Target', 'target.index', 'target', 'target.*|pengingat.*'],
        ['Profil', 'profil.index', 'profil', 'profil.*|rumah.*'],
    ];

    $namaDepan = \Illuminate\Support\Str::before($pengguna->name, ' ');
    $judul = trim($__env->yieldContent('judul')) ?: (trim($__env->yieldContent('page_title')) ?: trim($__env->yieldContent('title', 'CarbonTrace')));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $judul }} — CarbonTrace</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">

    {{-- beranda-022.css dimuat untuk token --ct-* sementara, sampai app.css versi Front-End Lead masuk --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/beranda-022.css', 'resources/css/aplikasi-111.css', 'resources/js/aplikasi-111.js'])
    @stack('aset')
    @stack('styles')
</head>
<body class="ap">
    <svg class="ap-simbol" aria-hidden="true" focusable="false">
        <symbol id="ct-lambang" viewBox="0 0 64 64">
            <path d="M32 4 56 18v28L32 60 8 46V18Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
            <path d="M24 36c0-12 8-20 20-20 0 12-8 20-20 20Z" fill="currentColor" fill-opacity=".18" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
            <path d="M24 36 38 22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <polyline points="16,46 25,40 32,43 40,35 48,38" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
    </svg>

    <a href="#isi" class="ap-lewati">Langsung ke isi</a>

    <aside class="ap-sisi">
        <a href="{{ route('beranda') }}" class="ap-merek">
            <svg class="ap-merek__lambang" aria-hidden="true" focusable="false"><use href="#ct-lambang"/></svg>
            <span class="ap-merek__teks">CARBONTRACE</span>
        </a>

        <nav aria-label="Menu aplikasi">
            <ul class="ap-menu">
                @foreach ($menu as [$label, $rute, $kunciIkon, $pola, $sub])
                    <li>
                        @if ($sub)
                            @php
                                $kelompokAktif = collect($sub)->contains(fn ($s) => $aktif($s[3]));
                                // Aktivitas dan Target terbuka sejak awal; pilihan buka/tutup pengguna diingat oleh aplikasi-111.js
                                $bukaBawaan = $kelompokAktif || in_array($kunciIkon, ['aktivitas', 'target'], true);
                            @endphp
                            <details class="ap-kelompok" data-kelompok="{{ $kunciIkon }}" @if ($kelompokAktif) data-kelompok-aktif @endif @if ($bukaBawaan) open @endif>
                                <summary class="ap-menu__tautan {{ $kelompokAktif ? 'ap-menu__tautan--induk-aktif' : '' }}" title="{{ $label }}">
                                    <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $ikon[$kunciIkon] !!}</svg>
                                    <span class="ap-menu__label">{{ $label }}</span>
                                    <svg class="ap-ikon ap-kelompok__panah" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m9 6 6 6-6 6"/></svg>
                                </summary>
                                <ul class="ap-submenu">
                                    @foreach ($sub as [$subLabel, $subRute, $subIkon, $subPola])
                                        <li>@include('layouts._tautan-menu-111', ['label' => $subLabel, 'rute' => $subRute, 'ikonSvg' => $ikon[$subIkon], 'aktifKah' => $aktif($subPola)])</li>
                                    @endforeach
                                </ul>
                            </details>
                        @else
                            @include('layouts._tautan-menu-111', ['label' => $label, 'rute' => $rute, 'ikonSvg' => $ikon[$kunciIkon], 'aktifKah' => $aktif($pola)])
                        @endif
                    </li>
                @endforeach
            </ul>
            {{-- Dijalankan langsung (bukan dari Vite) supaya kelompok yang pernah ditutup tidak sempat berkedip terbuka --}}
            <script>
                document.querySelectorAll('details.ap-kelompok[data-kelompok]:not([data-kelompok-aktif])').forEach(function (k) {
                    try { var s = localStorage.getItem('ct-menu-' + k.dataset.kelompok); if (s !== null) k.open = s === '1'; } catch (e) {}
                });
            </script>

            @if ($adminMenu)
                <p class="ap-menu__kelompok-judul">Administrator</p>
                <ul class="ap-menu">
                    @foreach ($adminMenu as [$label, $rute, $kunciIkon, $pola])
                        <li>@include('layouts._tautan-menu-111', ['label' => $label, 'rute' => $rute, 'ikonSvg' => $ikon[$kunciIkon], 'aktifKah' => $aktif($pola)])</li>
                    @endforeach
                </ul>
            @endif
        </nav>

        <form method="POST" action="{{ route('auth.keluar') }}" class="ap-keluar">
            @csrf
            <button type="submit" class="ap-menu__tautan" title="Keluar">
                <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/></svg>
                <span class="ap-menu__label">Keluar</span>
            </button>
        </form>
    </aside>

    <div class="ap-utama">
        <header class="ap-atas">
            <h1 class="ap-atas__judul">{{ $judul }}</h1>
            <div class="ap-atas__aksi">
                {{-- TODO: isi daftar pemberitahuan setelah pengumuman (PBI-18) siap --}}
                <button type="button" class="ap-lonceng" aria-label="Pemberitahuan (belum ada)">
                    <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 16V11a6 6 0 1 1 12 0v5l1.5 2h-15z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>
                </button>
                {{-- Avatar dan nama membuka halaman Profil Saya (modul Rosi) bila rutenya tersedia --}}
                @php $adaProfil = Route::has('profil.index'); @endphp
                <{{ $adaProfil ? 'a' : 'span' }} class="ap-pengguna"
                    @if ($adaProfil) href="{{ route('profil.index') }}" title="Buka profil" aria-label="Buka profil {{ $pengguna->name }}" @if (request()->routeIs('profil.*')) aria-current="page" @endif @endif>
                    <span class="ap-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pengguna->name, 0, 1)) }}</span>
                    <span class="ap-pengguna__nama">{{ $namaDepan }}</span>
                </{{ $adaProfil ? 'a' : 'span' }}>
                {{-- Di layar sempit sidebar tersembunyi, jadi tombol Keluar pindah ke sini --}}
                <form method="POST" action="{{ route('auth.keluar') }}" class="ap-keluar-atas">
                    @csrf
                    <button type="submit" aria-label="Keluar">
                        <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <main id="isi" class="ap-isi" tabindex="-1">
            @hasSection('content')
                <x-notifikasi />
                @yield('content')
            @else
                @yield('isi')
            @endif
        </main>
    </div>

    {{-- Layar sempit: lima menu utama di bawah layar --}}
    <nav class="ap-bawah" aria-label="Menu utama">
        @foreach ($menuBawah as [$label, $rute, $kunciIkon, $pola])
            @if (Route::has($rute))
                <a href="{{ route($rute) }}" class="ap-bawah__tautan" @if ($aktif($pola)) aria-current="page" @endif>
                    <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $ikon[$kunciIkon] !!}</svg>
                    <span>{{ $label }}</span>
                </a>
            @else
                <span class="ap-bawah__tautan ap-bawah__tautan--nanti" aria-disabled="true">
                    <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $ikon[$kunciIkon] !!}</svg>
                    <span>{{ $label }}</span>
                </span>
            @endif
        @endforeach
    </nav>

    @stack('scripts')
</body>
</html>
