{{--
    DATA CONTOH, BUKAN DATA NYATA.
    Seluruh angka, grafik, dan menu di bagian ini hanya tiruan tampilan dashboard
    untuk pengunjung yang belum punya akun. Tidak membaca basis data apa pun.
--}}
<section class="lp-bagian" id="pratinjau" aria-labelledby="lp-pratinjau-judul">
    <div class="lp-wadah">
        <header class="lp-kepala lp-kepala--tengah" data-muncul>
            <p class="lp-label">TAMPILAN</p>
            <h2 class="lp-judul-bagian" id="lp-pratinjau-judul">Angkanya langsung terlihat.</h2>
        </header>

        <div class="lp-pratinjau" data-muncul>
            {{-- Di layar sempit bingkai ini digeser mendatar di dalam wadahnya sendiri. --}}
            <div class="lp-pratinjau__gulir" tabindex="0" role="region" aria-label="Pratinjau dashboard">
                <div class="lp-jendela">
                    <div class="lp-jendela__bilah" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </div>

                    <div class="lp-jendela__isi">
                        <div class="lp-jendela__sisi" aria-hidden="true">
                            <svg class="lp-lambang lp-jendela__lambang" focusable="false"><use href="#ct-lambang"/></svg>
                            <span class="lp-jendela__menu lp-jendela__menu--aktif"></span>
                            <span class="lp-jendela__menu"></span>
                            <span class="lp-jendela__menu"></span>
                            <span class="lp-jendela__menu"></span>
                            <span class="lp-jendela__menu"></span>
                            <span class="lp-jendela__menu"></span>
                        </div>

                        <div class="lp-jendela__utama">
                            {{-- Data contoh: total emisi bulan berjalan --}}
                            <div class="lp-panel lp-panel--angka">
                                <p class="lp-panel__angka">112,4 <span>kg CO2e</span></p>
                                <p class="lp-panel__ket">dari target 167 kg</p>
                                <div class="lp-panel__kemajuan" aria-hidden="true"><span></span></div>
                            </div>

                            {{-- Data contoh: rincian per kategori (transportasi, listrik, makanan, sampah) --}}
                            <div class="lp-panel lp-panel--donat" aria-hidden="true">
                                <svg class="lp-donat" viewBox="0 0 42 42" focusable="false">
                                    <circle class="lp-donat__dasar" cx="21" cy="21" r="15.915"/>
                                    <circle class="lp-donat__ruas lp-donat__ruas--1" cx="21" cy="21" r="15.915" stroke-dasharray="44 56" stroke-dashoffset="25"/>
                                    <circle class="lp-donat__ruas lp-donat__ruas--2" cx="21" cy="21" r="15.915" stroke-dasharray="27 73" stroke-dashoffset="-20"/>
                                    <circle class="lp-donat__ruas lp-donat__ruas--3" cx="21" cy="21" r="15.915" stroke-dasharray="17 83" stroke-dashoffset="-48"/>
                                    <circle class="lp-donat__ruas lp-donat__ruas--4" cx="21" cy="21" r="15.915" stroke-dasharray="8 92" stroke-dashoffset="-66"/>
                                </svg>
                                <ul class="lp-donat__legenda">
                                    <li><span class="lp-titik lp-titik--1"></span><span class="lp-batang"></span></li>
                                    <li><span class="lp-titik lp-titik--2"></span><span class="lp-batang"></span></li>
                                    <li><span class="lp-titik lp-titik--3"></span><span class="lp-batang"></span></li>
                                    <li><span class="lp-titik lp-titik--4"></span><span class="lp-batang"></span></li>
                                </ul>
                            </div>

                            {{-- Data contoh: tren emisi harian 14 hari --}}
                            <div class="lp-panel lp-panel--tren" aria-hidden="true">
                                <svg class="lp-tren" viewBox="0 0 300 120" preserveAspectRatio="none" focusable="false">
                                    <defs>
                                        <linearGradient id="lp-tren-isi" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0" class="lp-tren__stop-atas"/>
                                            <stop offset="1" class="lp-tren__stop-bawah"/>
                                        </linearGradient>
                                    </defs>
                                    <path class="lp-tren__kisi" d="M0 20H300M0 50H300M0 80H300"/>
                                    <path class="lp-tren__sumbu" d="M0 110H300"/>
                                    <path class="lp-tren__target" d="M0 40H300"/>
                                    <polygon fill="url(#lp-tren-isi)" points="10,34 31,42 53,30 74,48 96,44 117,56 139,50 160,62 182,58 203,70 225,66 246,76 268,72 290,82 290,110 10,110"/>
                                    <polyline class="lp-tren__garis" points="10,34 31,42 53,30 74,48 96,44 117,56 139,50 160,62 182,58 203,70 225,66 246,76 268,72 290,82"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data contoh: kartu kecil mengambang --}}
            <div class="lp-apung lp-apung--1">
                <span class="lp-apung__angka">7</span>
                <span class="lp-apung__ket">hari beruntun</span>
            </div>
            <div class="lp-apung lp-apung--2">
                <span class="lp-apung__angka">1.840</span>
                <span class="lp-apung__ket">EcoPoint</span>
            </div>
            <div class="lp-apung lp-apung--3">
                <span class="lp-apung__angka">67%</span>
                <span class="lp-apung__ket">terpakai</span>
            </div>
        </div>
    </div>
</section>
