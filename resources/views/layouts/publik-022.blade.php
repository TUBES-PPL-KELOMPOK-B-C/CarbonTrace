<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'CarbonTrace')</title>
    <meta name="description" content="@yield('deskripsi', 'Platform Pelacakan dan Pemangkasan Jejak Karbon Harian')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">

    @vite(['resources/css/app.css', 'resources/css/beranda-022.css', 'resources/js/beranda-022.js'])
    @stack('aset')
</head>
<body class="lp @yield('kelas-body')">
    {{-- Lambang CarbonTrace: heksagon berisi daun dan garis grafik. Didefinisikan sekali, dipakai ulang lewat <use>. --}}
    <svg class="lp-simbol" aria-hidden="true" focusable="false">
        <symbol id="ct-lambang" viewBox="0 0 64 64">
            <path d="M32 4 56 18v28L32 60 8 46V18Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
            <path d="M24 36c0-12 8-20 20-20 0 12-8 20-20 20Z" fill="currentColor" fill-opacity=".18" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
            <path d="M24 36 38 22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <polyline points="16,46 25,40 32,43 40,35 48,38" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
    </svg>

    @yield('isi')
</body>
</html>
