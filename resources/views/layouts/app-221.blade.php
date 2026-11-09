<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CarbonTrace') — Pelacakan Jejak Karbon Harian</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-[#06130E] text-[#E8EDEB] min-h-screen font-['Plus_Jakarta_Sans',sans-serif] flex flex-col md:flex-row antialiased selection:bg-emerald-500 selection:text-black">

    <!-- Sidebar (Desktop / Tablet) -->
    <aside id="sidebar-utama" class="hidden md:flex flex-col w-20 lg:w-60 shrink-0 min-h-screen border-r border-[#1E2B26] bg-[#111A17] transition-all duration-200 sticky top-0 h-screen z-30">
        <!-- Logo -->
        <div class="h-[72px] flex items-center px-4 lg:px-6 border-b border-[#1E2B26]">
            <a href="/" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#10B981] to-[#A3E635] flex items-center justify-center text-black font-extrabold text-lg shadow-lg shadow-emerald-500/20">
                    C
                </div>
                <span class="hidden lg:inline text-lg font-bold tracking-tight text-white">Carbon<span class="text-[#10B981]">Trace</span></span>
            </a>
        </div>

        <!-- Navigasi Utama (8 menu baku) -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @php
                $menuItems = [
                    ['url' => '/dashboard', 'route' => 'dashboard*', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['url' => route('aktivitas.index', ['buka' => 'buat']), 'route' => 'aktivitas.create', 'label' => 'Catat Aktivitas', 'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['url' => route('aktivitas.index'), 'route' => 'aktivitas.*', 'label' => 'Riwayat', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['url' => route('template.index'), 'route' => 'template.*', 'label' => 'Template Rutin', 'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                    ['url' => '/target', 'route' => 'target*', 'label' => 'Target', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    ['url' => '/tantangan', 'route' => 'tantangan*', 'label' => 'Tantangan', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                    ['url' => '/peringkat', 'route' => 'peringkat*', 'label' => 'Papan Peringkat', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['url' => '/edukasi', 'route' => 'edukasi*', 'label' => 'Edukasi', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ['url' => '/profil', 'route' => 'profil*', 'label' => 'Profil', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ];
            @endphp

            @foreach($menuItems as $item)
                @php
                    $isAktif = request()->routeIs($item['route']);
                @endphp
                <a href="{{ $item['url'] }}"
                   class="group flex items-center gap-3.5 px-3 py-3 rounded-xl text-sm font-semibold transition-all duration-150 {{ $isAktif ? 'bg-[#1A2622] text-[#10B981] border-l-4 border-[#10B981]' : 'text-[#8CA099] hover:bg-[#1A2622]/60 hover:text-[#E8EDEB] border-l-4 border-transparent' }}"
                   title="{{ $item['label'] }}">
                    <svg class="w-5 h-5 shrink-0 {{ $isAktif ? 'text-[#10B981]' : 'text-[#8CA099] group-hover:text-[#E8EDEB]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                    </svg>
                    <span class="hidden lg:inline truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach

            <!-- Bagian Admin (PBI-06 Bank Kuis dll) -->
            <div class="pt-6 mt-6 border-t border-[#1E2B26]">
                <div class="hidden lg:block px-3 mb-2 text-xs font-bold uppercase tracking-wider text-[#8CA099]/70">
                    Administrator
                </div>
                <a href="{{ route('kuis.index') }}"
                   class="group flex items-center gap-3.5 px-3 py-3 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('kuis.*') ? 'bg-[#1A2622] text-[#10B981] border-l-4 border-[#10B981]' : 'text-[#8CA099] hover:bg-[#1A2622]/60 hover:text-[#E8EDEB] border-l-4 border-transparent' }}"
                   title="Bank Soal Kuis">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('kuis.*') ? 'text-[#10B981]' : 'text-[#8CA099] group-hover:text-[#E8EDEB]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="hidden lg:inline truncate">Bank Soal Kuis</span>
                </a>
            </div>
        </nav>

        <!-- Profil Bawah -->
        <div class="p-3 border-t border-[#1E2B26] hidden lg:block">
            <div class="flex items-center gap-3 p-2 rounded-xl bg-[#1A2622]/40">
                <div class="w-9 h-9 rounded-full bg-emerald-900/60 border border-emerald-500/30 flex items-center justify-center font-bold text-sm text-[#10B981]">
                    {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'U' }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->check() ? auth()->user()->name : 'Tamu Pengguna' }}</p>
                    <p class="text-xs text-[#8CA099] truncate">{{ auth()->check() ? auth()->user()->email : 'tamu@carbontrace.id' }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Konten Utama -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Bilah Atas (72px) -->
        <header class="h-[72px] px-4 md:px-8 flex items-center justify-between border-b border-[#1E2B26] bg-[#111A17]/80 backdrop-blur sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <!-- Mobile Logo -->
                <div class="md:hidden flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#10B981] to-[#A3E635] flex items-center justify-center text-black font-extrabold text-base">
                        C
                    </div>
                    <span class="font-bold text-white text-base">Carbon<span class="text-[#10B981]">Trace</span></span>
                </div>
                <div class="hidden md:block">
                    <h1 class="text-lg font-bold text-white">@yield('page_title', 'Pencatatan Aktivitas')</h1>
                </div>
            </div>

            <!-- Bilah Kanan -->
            <div class="flex items-center gap-3">
                <a href="{{ route('aktivitas.index', ['buka' => 'buat']) }}"
                   class="ct-tombol ct-tombol--utama text-xs md:text-sm !h-10 !px-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Catat Emisi</span>
                </a>
            </div>
        </header>

        <!-- Isi Halaman -->
        <main class="flex-1 p-4 md:p-8 max-w-[1400px] w-full mx-auto pb-24 md:pb-8">
            <x-notifikasi />
            @yield('content')
        </main>
    </div>

    <!-- Navigasi Bawah (Mobile / 375px) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-[#111A17] border-t border-[#1E2B26] flex items-center justify-around z-30 px-2">
        <a href="/dashboard" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('dashboard*') ? 'text-[#10B981]' : 'text-[#8CA099]' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('aktivitas.index') }}" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('aktivitas.*') ? 'text-[#10B981]' : 'text-[#8CA099]' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Riwayat</span>
        </a>
        <!-- Tombol Bulat Tambah -->
        <a href="{{ route('aktivitas.index', ['buka' => 'buat']) }}" class="-mt-6 w-12 h-12 rounded-full bg-gradient-to-tr from-[#10B981] to-[#A3E635] flex items-center justify-center text-black shadow-lg shadow-emerald-500/30">
            <svg class="w-6 h-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        </a>
        <a href="{{ route('template.index') }}" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('template.*') ? 'text-[#10B981]' : 'text-[#8CA099]' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
            <span>Template</span>
        </a>
        <a href="/profil" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('profil*') ? 'text-[#10B981]' : 'text-[#8CA099]' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Profil</span>
        </a>
    </nav>

    @stack('scripts')
</body>
</html>
