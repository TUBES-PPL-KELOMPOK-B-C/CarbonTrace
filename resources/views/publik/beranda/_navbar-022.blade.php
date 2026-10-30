{{-- Lambang CarbonTrace: heksagon berisi daun dan garis grafik. Didefinisikan sekali, dipakai ulang lewat <use>. --}}
<svg class="lp-simbol" aria-hidden="true" focusable="false">
    <symbol id="ct-lambang" viewBox="0 0 64 64">
        <path d="M32 4 56 18v28L32 60 8 46V18Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
        <path d="M24 36c0-12 8-20 20-20 0 12-8 20-20 20Z" fill="currentColor" fill-opacity=".18" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
        <path d="M24 36 38 22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <polyline points="16,46 25,40 32,43 40,35 48,38" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
</svg>

<header class="lp-nav" data-nav>
    <div class="lp-nav__pelat">
        <a href="#beranda" class="lp-merek">
            <svg class="lp-lambang lp-lambang--kecil" aria-hidden="true" focusable="false"><use href="#ct-lambang"/></svg>
            <span class="lp-merek__teks">
                <span class="lp-merek__nama">CARBONTRACE</span>
                <span class="lp-merek__sub">TELKOM UNIVERSITY</span>
            </span>
        </a>

        <nav class="lp-nav__menu" id="lp-menu" aria-label="Navigasi utama" data-nav-menu>
            <ul class="lp-nav__tautan" data-nav-tautan>
                <li><a href="#beranda">Beranda</a></li>
                <li><a href="#fitur">Fitur</a></li>
                <li><a href="#cara-kerja">Cara Kerja</a></li>
                <li><a href="#pratinjau">Kalkulator</a></li>
                <li><a href="#edukasi">Edukasi</a></li>
                <li><a href="#tentang">Tentang</a></li>
            </ul>

            {{-- Salinan tombol untuk panel menu di layar sempit; disembunyikan di layar lebar. --}}
            <div class="lp-nav__aksi lp-nav__aksi--panel">
                {{-- TODO: arahkan ke route('auth.masuk') setelah KF-01 selesai dikerjakan Rosi --}}
                <a href="#" class="lp-pelat-tombol lp-pelat-tombol--hantu">
                    <svg class="lp-ikon-kecil" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></svg>
                    Masuk
                </a>
                {{-- TODO: arahkan ke route('auth.daftar') setelah KF-01 selesai dikerjakan Rosi --}}
                <a href="#" class="lp-pelat-tombol lp-pelat-tombol--isi">
                    <svg class="lp-ikon-kecil" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 19c0-8 5-14 14-14 0 9-6 14-14 14Z"/><path d="M5 19 13 11"/></svg>
                    Daftar Gratis
                </a>
            </div>
        </nav>

        <div class="lp-nav__aksi">
            {{-- TODO: arahkan ke route('auth.masuk') setelah KF-01 selesai dikerjakan Rosi --}}
            <a href="#" class="lp-pelat-tombol lp-pelat-tombol--hantu">
                <svg class="lp-ikon-kecil" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></svg>
                Masuk
            </a>
            {{-- TODO: arahkan ke route('auth.daftar') setelah KF-01 selesai dikerjakan Rosi --}}
            <a href="#" class="lp-pelat-tombol lp-pelat-tombol--isi">
                <svg class="lp-ikon-kecil" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 19c0-8 5-14 14-14 0 9-6 14-14 14Z"/><path d="M5 19 13 11"/></svg>
                Daftar Gratis
            </a>
        </div>

        <button type="button" class="lp-nav__burger" aria-expanded="false" aria-controls="lp-menu" aria-label="Buka menu" data-nav-burger>
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </div>
</header>
