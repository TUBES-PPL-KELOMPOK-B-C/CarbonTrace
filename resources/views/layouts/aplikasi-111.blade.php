@php
    // Kerangka halaman setelah masuk: sidebar kiri, bilah atas, isi di kanan; di layar sempit
    // sidebar menjadi menu bawah lima ikon. Menu yang halamannya belum dibuat pemiliknya tampil
    // redup dan otomatis aktif begitu rutenya terdaftar.
    $menu = [
        ['Dashboard', 'dashboard.index', '<path d="M4 13h6V4H4zM14 20h6v-9h-6zM14 4v4h6V4zM4 20h6v-4H4z"/>', true],
        ['Catat Aktivitas', 'aktivitas.create', '<circle cx="12" cy="12" r="8"/><path d="M12 8v8M8 12h8"/>', true],
        ['Riwayat', 'aktivitas.index', '<path d="M4 12a8 8 0 1 0 2.3-5.7L4 8.6"/><path d="M4 4v4.6h4.6M12 8v4l3 2"/>', true],
        ['Target', 'target.index', '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r=".8"/>', true],
        ['Tantangan', 'ikut.daftar', '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>', false],
        ['Papan Peringkat', 'peringkat.index', '<path d="M8 21V11H4v10zM14 21V5h-4v16zM20 21v-7h-4v7z"/>', true],
        ['Edukasi', 'artikel.publik', '<path d="M4 5h6a2 2 0 0 1 2 2v13a2 2 0 0 0-2-2H4zM20 5h-6a2 2 0 0 0-2 2v13a2 2 0 0 1 2-2h6z"/>', false],
        ['Rumah Tangga', 'rumah.index', '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>', true],
        ['Profil', 'profil.index', '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>', true],
    ];
    $pengguna = auth()->user();
    $namaDepan = \Illuminate\Support\Str::before($pengguna->name, ' ');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') — CarbonTrace</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">

    {{-- beranda-022.css dimuat untuk token --ct-* sementara, sampai app.css versi Front-End Lead masuk --}}
    @vite(['resources/css/app.css', 'resources/css/beranda-022.css', 'resources/css/aplikasi-111.css'])
    @stack('aset')
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
                @foreach ($menu as [$label, $rute, $ikon])
                    <li>
                        @if (Route::has($rute))
                            <a href="{{ route($rute) }}" class="ap-menu__tautan" title="{{ $label }}"
                               @if (request()->routeIs($rute)) aria-current="page" @endif>
                                <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $ikon !!}</svg>
                                <span class="ap-menu__label">{{ $label }}</span>
                            </a>
                        @else
                            <span class="ap-menu__tautan ap-menu__tautan--nanti" aria-disabled="true" title="{{ $label }} (segera hadir)">
                                <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $ikon !!}</svg>
                                <span class="ap-menu__label">{{ $label }}</span>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
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
            <h1 class="ap-atas__judul">@yield('judul')</h1>
            <div class="ap-atas__aksi">
                {{-- TODO: isi daftar pemberitahuan setelah pengingat (PBI-11) dan pengumuman (PBI-18) siap --}}
                <button type="button" class="ap-lonceng" aria-label="Pemberitahuan (belum ada)">
                    <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 16V11a6 6 0 1 1 12 0v5l1.5 2h-15z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>
                </button>
                <span class="ap-pengguna">
                    <span class="ap-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pengguna->name, 0, 1)) }}</span>
                    <span class="ap-pengguna__nama">{{ $namaDepan }}</span>
                </span>
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
            @yield('isi')
        </main>
    </div>

    {{-- Layar sempit: lima menu utama di bawah layar --}}
    <nav class="ap-bawah" aria-label="Menu utama">
        @foreach (array_filter($menu, fn ($m) => $m[3]) as [$label, $rute, $ikon])
            @if (Route::has($rute))
                <a href="{{ route($rute) }}" class="ap-bawah__tautan" @if (request()->routeIs($rute)) aria-current="page" @endif>
                    <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $ikon !!}</svg>
                    <span>{{ \Illuminate\Support\Str::before($label, ' ') }}</span>
                </a>
            @else
                <span class="ap-bawah__tautan ap-bawah__tautan--nanti" aria-disabled="true">
                    <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $ikon !!}</svg>
                    <span>{{ \Illuminate\Support\Str::before($label, ' ') }}</span>
                </span>
            @endif
        @endforeach
    </nav>
</body>
</html>
