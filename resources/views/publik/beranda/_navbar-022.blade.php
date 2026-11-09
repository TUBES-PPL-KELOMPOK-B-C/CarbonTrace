@php
    // Tombol kanan navbar: tamu melihat Masuk/Daftar, pengguna yang sudah masuk melihat namanya dan Keluar.
    // Dipakai dua kali: di pelat navbar dan di panel menu layar sempit.
    $pengguna = auth()->user();

    // Di halaman selain beranda, tautan bagian (#fitur dan lainnya) diarahkan kembali ke beranda.
    $dasar = request()->routeIs('beranda') ? '' : route('beranda');
    $diKalkulator = request()->routeIs('kalkulator.*');
@endphp

<header class="lp-nav" data-nav>
    <div class="lp-nav__pelat">
        <a href="{{ $dasar }}#beranda" class="lp-merek">
            <svg class="lp-lambang lp-lambang--kecil" aria-hidden="true" focusable="false"><use href="#ct-lambang"/></svg>
            <span class="lp-merek__teks">
                <span class="lp-merek__nama">CARBONTRACE</span>
                <span class="lp-merek__sub">TELKOM UNIVERSITY</span>
            </span>
        </a>

        <nav class="lp-nav__menu" id="lp-menu" aria-label="Navigasi utama" data-nav-menu>
            <ul class="lp-nav__tautan" data-nav-tautan>
                <li><a href="{{ $dasar }}#beranda">Beranda</a></li>
                <li><a href="{{ $dasar }}#fitur">Fitur</a></li>
                <li><a href="{{ $dasar }}#cara-kerja">Cara Kerja</a></li>
                <li><a href="{{ Route::has('kalkulator.index') ? route('kalkulator.index') : $dasar.'#pratinjau' }}" @if ($diKalkulator) aria-current="page" @endif>Kalkulator</a></li>
                <li><a href="{{ $dasar }}#edukasi">Edukasi</a></li>
                <li><a href="{{ $dasar }}#tentang">Tentang</a></li>
            </ul>

            {{-- Salinan tombol untuk panel menu di layar sempit; disembunyikan di layar lebar. --}}
            <div class="lp-nav__aksi lp-nav__aksi--panel">
                @include('publik.beranda._navbar-aksi-022', ['pengguna' => $pengguna])
            </div>
        </nav>

        <div class="lp-nav__aksi">
            @include('publik.beranda._navbar-aksi-022', ['pengguna' => $pengguna])
        </div>

        <button type="button" class="lp-nav__burger" aria-expanded="false" aria-controls="lp-menu" aria-label="Buka menu" data-nav-burger>
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </div>
</header>
