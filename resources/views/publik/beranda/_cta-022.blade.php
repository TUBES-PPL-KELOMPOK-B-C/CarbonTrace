<section class="lp-bagian" aria-labelledby="lp-cta-judul">
    <div class="lp-wadah">
        <div class="lp-cta" data-muncul>
            {{-- Pola kisi karbon samar, ubin heksagonal yang diulang --}}
            <svg class="lp-cta__pola" aria-hidden="true" focusable="false">
                <defs>
                    <pattern id="lp-pola-heksagon" width="55.43" height="96" patternUnits="userSpaceOnUse">
                        <path class="lp-cta__garis" d="M27.71 0v16M27.71 16 0 32M27.71 16l27.72 16M0 32v32M55.43 32v32M0 64l27.71 16M55.43 64 27.71 80M27.71 80v16"/>
                        <circle class="lp-cta__simpul" cx="27.71" cy="16" r="2"/>
                        <circle class="lp-cta__simpul" cx="0" cy="32" r="2"/>
                        <circle class="lp-cta__simpul" cx="55.43" cy="32" r="2"/>
                        <circle class="lp-cta__simpul" cx="0" cy="64" r="2"/>
                        <circle class="lp-cta__simpul" cx="55.43" cy="64" r="2"/>
                        <circle class="lp-cta__simpul" cx="27.71" cy="80" r="2"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#lp-pola-heksagon)"/>
            </svg>

            <h2 class="lp-cta__judul" id="lp-cta-judul">Mulai dari satu catatan hari ini.</h2>
            <p class="lp-cta__sub">Gratis, tanpa iklan, dan datanya milikmu sendiri.</p>

            <div class="lp-cta__aksi">
                {{-- TODO: arahkan ke route('auth.daftar') setelah KF-01 selesai dikerjakan Rosi --}}
                <a href="#" class="ct-tombol ct-tombol--utama lp-tombol-besar">Daftar Gratis</a>
                {{-- TODO: arahkan ke route('auth.masuk') setelah KF-01 selesai dikerjakan Rosi --}}
                <a href="#" class="ct-tombol ct-tombol--hantu lp-tombol-besar lp-tombol-berbingkai">Masuk</a>
            </div>
        </div>
    </div>
</section>
