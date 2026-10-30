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
</head>
<body class="lp">
    @yield('isi')
</body>
</html>
