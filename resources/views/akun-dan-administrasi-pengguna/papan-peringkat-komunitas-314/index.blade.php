@extends('layouts.aplikasi-111')

@section('judul', 'Papan Peringkat')

@push('aset')
    <link rel="stylesheet" href="{{ asset('build/assets/papan-peringkat-314.css') }}">
@endpush

@section('isi')
<div class="ct-peringkat-wadah">
    {{-- Header Papan Peringkat --}}
    <div class="ct-peringkat-header">
        <div class="ct-peringkat-intro">
            <div>
                <h2 class="ct-peringkat-judul">Papan Peringkat Komunitas</h2>
                <p class="ct-peringkat-sub">Bersaing positif bersama civitas akademika dalam memangkas jejak karbon harian.</p>
            </div>
            <div class="ct-peringkat-catatan-waktu" title="Peringkat diperbarui secara berkala melalui sistem cache">
                <span class="titik"></span>
                <span>Diperbarui beberapa menit lalu</span>
            </div>
        </div>

        {{-- Komponen Filter dan Form --}}
        @include('akun-dan-administrasi-pengguna.papan-peringkat-komunitas-314.form')
    </div>

    {{-- Wadah Animasi Memuat (Hidden by default, used during AJAX) --}}
    <div id="wadah-memuat" class="ct-tabel-kartu" style="display: none;" aria-hidden="true">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--ct-border);">
            <div class="ct-blok-kerangka" style="width: 30%;"></div>
        </div>
        @for ($i = 0; $i < 8; $i++)
            <div class="ct-baris-kerangka">
                <div class="ct-blok-kerangka" style="width: 40px;"></div>
                <div class="ct-blok-kerangka" style="width: 36px; height: 36px; border-radius: 50%;"></div>
                <div class="ct-blok-kerangka" style="width: 35%;"></div>
                <div class="ct-blok-kerangka" style="width: 15%; margin-left: auto;"></div>
                <div class="ct-blok-kerangka" style="width: 12%;"></div>
            </div>
        @endfor
    </div>

    {{-- Wadah Galat (Hidden by default) --}}
    <div id="wadah-galat" class="ct-keadaan-wadah" style="display: none;">
        <svg class="ct-keadaan-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <h3 class="ct-keadaan-judul">Gagal Memuat Data Papan Peringkat</h3>
        <p class="ct-keadaan-teks">Terjadi kendala saat menghubungkan ke sistem peringkat. Silakan coba kembali.</p>
        <button type="button" class="ct-tombol ct-tombol--utama" onclick="muatUlangData()">Coba lagi</button>
    </div>

    {{-- Konten Utama Papan Peringkat --}}
    <div id="wadah-konten">
        @if ($totalPeserta === 0)
            {{-- Keadaan Kosong --}}
            <div class="ct-keadaan-wadah">
                <svg class="ct-keadaan-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <h3 class="ct-keadaan-judul">Papan Peringkat Masih Kosong</h3>
                <p class="ct-keadaan-teks">Papan peringkat masih kosong. Catat aktivitas pertamamu untuk mulai mengumpulkan poin.</p>
                <a href="{{ Route::has('aktivitas.create') ? route('aktivitas.create') : route('dashboard.index') }}" class="ct-tombol ct-tombol--utama">
                    Catat Aktivitas Sekarang
                </a>
            </div>
        @else
            {{-- Podium 3 Besar --}}
            @if (count($podium) >= 3)
                <div class="ct-podium-wadah" id="wadah-podium">
                    {{-- Juara 2 (Kiri) --}}
                    @php $p2 = $podium[1]; @endphp
                    <div class="ct-podium-item ct-podium-item--2">
                        <div class="ct-podium-lencana">2</div>
                        <div class="ct-podium-foto-wadah">
                            @if ($p2['foto'])
                                <img src="{{ $p2['foto'] }}" alt="{{ $p2['nama'] }}" class="ct-podium-foto">
                            @else
                                <div class="ct-podium-foto">{{ \Illuminate\Support\Str::upper(substr($p2['nama'], 0, 1)) }}</div>
                            @endif
                        </div>
                        <h4 class="ct-podium-nama" title="{{ $p2['nama'] }}">{{ $p2['nama'] }}</h4>
                        <div class="ct-podium-skor">
                            <span class="ct-podium-angka">{{ number_format($p2['poin'], 0, ',', '.') }}</span>
                            <span class="ct-podium-satuan">Poin Jejak</span>
                        </div>
                        <div class="ct-podium-badge-bar">
                            <span class="ct-pelat ct-pelat--aman">🔥 {{ $p2['streak'] }} Hari</span>
                            @if ($p2['penurunan'] > 0)
                                <span class="ct-pelat ct-pelat--hampir">📉 {{ number_format($p2['penurunan'], 1, ',', '.') }}%</span>
                            @endif
                        </div>
                    </div>

                    {{-- Juara 1 (Tengah) --}}
                    @php $p1 = $podium[0]; @endphp
                    <div class="ct-podium-item ct-podium-item--1">
                        <div class="ct-podium-lencana">👑 1</div>
                        <div class="ct-podium-foto-wadah">
                            @if ($p1['foto'])
                                <img src="{{ $p1['foto'] }}" alt="{{ $p1['nama'] }}" class="ct-podium-foto">
                            @else
                                <div class="ct-podium-foto">{{ \Illuminate\Support\Str::upper(substr($p1['nama'], 0, 1)) }}</div>
                            @endif
                        </div>
                        <h4 class="ct-podium-nama" title="{{ $p1['nama'] }}">{{ $p1['nama'] }}</h4>
                        <div class="ct-podium-skor">
                            <span class="ct-podium-angka" style="color: #F59E0B;">{{ number_format($p1['poin'], 0, ',', '.') }}</span>
                            <span class="ct-podium-satuan">Poin Jejak</span>
                        </div>
                        <div class="ct-podium-badge-bar">
                            <span class="ct-pelat ct-pelat--aman">🔥 {{ $p1['streak'] }} Hari</span>
                            @if ($p1['penurunan'] > 0)
                                <span class="ct-pelat ct-pelat--hampir">📉 {{ number_format($p1['penurunan'], 1, ',', '.') }}%</span>
                            @endif
                        </div>
                    </div>

                    {{-- Juara 3 (Kanan) --}}
                    @php $p3 = $podium[2]; @endphp
                    <div class="ct-podium-item ct-podium-item--3">
                        <div class="ct-podium-lencana">3</div>
                        <div class="ct-podium-foto-wadah">
                            @if ($p3['foto'])
                                <img src="{{ $p3['foto'] }}" alt="{{ $p3['nama'] }}" class="ct-podium-foto">
                            @else
                                <div class="ct-podium-foto">{{ \Illuminate\Support\Str::upper(substr($p3['nama'], 0, 1)) }}</div>
                            @endif
                        </div>
                        <h4 class="ct-podium-nama" title="{{ $p3['nama'] }}">{{ $p3['nama'] }}</h4>
                        <div class="ct-podium-skor">
                            <span class="ct-podium-angka">{{ number_format($p3['poin'], 0, ',', '.') }}</span>
                            <span class="ct-podium-satuan">Poin Jejak</span>
                        </div>
                        <div class="ct-podium-badge-bar">
                            <span class="ct-pelat ct-pelat--aman">🔥 {{ $p3['streak'] }} Hari</span>
                            @if ($p3['penurunan'] > 0)
                                <span class="ct-pelat ct-pelat--hampir">📉 {{ number_format($p3['penurunan'], 1, ',', '.') }}%</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tabel Peringkat Keseluruhan --}}
            <div class="ct-tabel-kartu">
                <table class="ct-tabel-peringkat" aria-label="Tabel Papan Peringkat">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 80px;">Peringkat</th>
                            <th scope="col">Pengguna</th>
                            <th scope="col" class="angka" style="text-align: right;">Total Poin</th>
                            <th scope="col" class="angka" style="text-align: right;">Streak Berjalan</th>
                            <th scope="col" class="angka" style="text-align: right;">Penurunan Emisi</th>
                        </tr>
                    </thead>
                    <tbody id="tubuh-tabel-peringkat">
                        @foreach ($dataTabel as $baris)
                            @php
                                $adalahSaya = (int) $baris['user_id'] === (int) auth()->id();
                            @endphp
                            <tr class="{{ $adalahSaya ? 'ct-baris-saya' : '' }}">
                                <td data-label="Peringkat" class="ct-kolom-peringkat">
                                    <span aria-label="Peringkat {{ $baris['peringkat'] }} dari {{ $totalPeserta }}">
                                        #{{ $baris['peringkat'] }}
                                    </span>
                                </td>
                                <td data-label="Pengguna">
                                    <div class="ct-kolom-pengguna">
                                        @if ($baris['foto'])
                                            <img src="{{ $baris['foto'] }}" alt="{{ $baris['nama'] }}" class="ct-mini-avatar">
                                        @else
                                            <span class="ct-mini-avatar">{{ \Illuminate\Support\Str::upper(substr($baris['nama'], 0, 1)) }}</span>
                                        @endif
                                        <span class="ct-nama-pengguna">
                                            {{ $baris['nama'] }}
                                            @if ($adalahSaya)
                                                <span class="ct-pelat-kamu">Kamu</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Total Poin" class="angka" style="text-align: right; font-weight: 700; color: var(--ct-accent);">
                                    {{ number_format($baris['poin'], 0, ',', '.') }}
                                </td>
                                <td data-label="Streak" class="angka" style="text-align: right;">
                                    🔥 {{ $baris['streak'] }} hari
                                </td>
                                <td data-label="Penurunan" class="angka" style="text-align: right; color: var(--ct-primary);">
                                    {{ $baris['penurunan'] > 0 ? number_format($baris['penurunan'], 1, ',', '.') . '%' : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Baris Menempel di Bawah Khusus Pengguna Aktif (Sticky Saya) --}}
    @if ($totalPeserta > 0 && isset($saya))
        <aside class="ct-baris-saya-tetap" aria-label="Ringkasan Peringkat Saya">
            <div class="ct-saya-info">
                <span class="ct-mini-avatar" style="width: 44px; height: 44px; font-size: 16px;">
                    {{ \Illuminate\Support\Str::upper(substr(auth()->user()->name, 0, 1)) }}
                </span>
                <div>
                    <div class="ct-saya-peringkat">
                        #<span id="saya-peringkat-angka">{{ $saya['peringkat'] }}</span>
                        <small>dari {{ $totalPeserta }} peserta</small>
                    </div>
                    <span style="font-size: 12px; color: var(--ct-text-muted);">Posisi Kamu di Komunitas</span>
                </div>
            </div>
            <div class="ct-saya-metrik">
                <div class="ct-saya-metrik-item">
                    <span class="ct-saya-metrik-label">Poin Kamu</span>
                    <span class="ct-saya-metrik-nilai" id="saya-poin-angka" style="color: var(--ct-accent);">
                        {{ number_format($saya['poin'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="ct-saya-metrik-item">
                    <span class="ct-saya-metrik-label">Streak Berjalan</span>
                    <span class="ct-saya-metrik-nilai" id="saya-streak-angka">
                        🔥 {{ $saya['streak'] }} hari
                    </span>
                </div>
                <div class="ct-saya-metrik-item">
                    <span class="ct-saya-metrik-label">Penurunan Emisi</span>
                    <span class="ct-saya-metrik-nilai" id="saya-penurunan-angka" style="color: var(--ct-primary);">
                        {{ $saya['penurunan'] > 0 ? number_format($saya['penurunan'], 1, ',', '.') . '%' : '-' }}
                    </span>
                </div>
            </div>
        </aside>
    @endif
</div>

{{-- Skrip Interaktivitas Tab & API Papan Peringkat (Tanpa Reload Halaman) --}}
<script>
    let statePeriode = '{{ $periode }}';
    let stateUrut = '{{ $urut }}';
    const myUserId = {{ auth()->id() ?? 0 }};

    function gantiPeriode(periodeBaru) {
        if (statePeriode === periodeBaru) return;
        statePeriode = periodeBaru;

        // Perbarui atribut aria dan kelas aktif pada tab
        document.querySelectorAll('.ct-tab-item').forEach(btn => {
            btn.classList.remove('ct-tab-item--aktif');
            btn.setAttribute('aria-selected', 'false');
        });
        const tabAktif = document.getElementById('tab-' + periodeBaru);
        if (tabAktif) {
            tabAktif.classList.add('ct-tab-item--aktif');
            tabAktif.setAttribute('aria-selected', 'true');
        }

        muatDataDariApi();
    }

    function gantiUrutan(urutanBaru) {
        if (stateUrut === urutanBaru) return;
        stateUrut = urutanBaru;
        muatDataDariApi();
    }

    function muatUlangData() {
        muatDataDariApi();
    }

    async function muatDataDariApi() {
        const wadahKonten = document.getElementById('wadah-konten');
        const wadahMemuat = document.getElementById('wadah-memuat');
        const wadahGalat = document.getElementById('wadah-galat');

        wadahMemuat.style.display = 'block';
        wadahGalat.style.display = 'none';
        if (wadahKonten) wadahKonten.style.opacity = '0.4';

        try {
            const url = `{{ route('peringkat.data') }}?periode=${statePeriode}&urut=${stateUrut}&halaman=1`;
            const respon = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!respon.ok) throw new Error('Respon tidak valid');

            const hasil = await respon.json();
            renderPeringkat(hasil);

            wadahMemuat.style.display = 'none';
            if (wadahKonten) wadahKonten.style.opacity = '1';
        } catch (error) {
            console.error('Galat memuat papan peringkat:', error);
            wadahMemuat.style.display = 'none';
            wadahGalat.style.display = 'block';
            if (wadahKonten) wadahKonten.style.display = 'none';
        }
    }

    function formatAngka(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function renderPeringkat(json) {
        const tbody = document.getElementById('tubuh-tabel-peringkat');
        if (!tbody) {
            // Halaman awal kosong, muat ulang halaman sederhana
            window.location.reload();
            return;
        }

        tbody.innerHTML = '';
        const list = json.data || [];
        const total = json.total_peserta || list.length;

        list.forEach(item => {
            const adalahSaya = Number(item.user_id) === myUserId;
            const tr = document.createElement('tr');
            if (adalahSaya) tr.classList.add('ct-baris-saya');

            const avatarHtml = item.foto 
                ? `<img src="${item.foto}" alt="${item.nama}" class="ct-mini-avatar">`
                : `<span class="ct-mini-avatar">${(item.nama || 'U').charAt(0).toUpperCase()}</span>`;

            const kamuBadge = adalahSaya ? '<span class="ct-pelat-kamu">Kamu</span>' : '';
            const penurunanText = item.penurunan > 0 ? `${formatAngka(item.penurunan)}%` : '-';

            tr.innerHTML = `
                <td data-label="Peringkat" class="ct-kolom-peringkat">
                    <span aria-label="Peringkat ${item.peringkat} dari ${total}">#${item.peringkat}</span>
                </td>
                <td data-label="Pengguna">
                    <div class="ct-kolom-pengguna">
                        ${avatarHtml}
                        <span class="ct-nama-pengguna">${item.nama} ${kamuBadge}</span>
                    </div>
                </td>
                <td data-label="Total Poin" class="angka" style="text-align: right; font-weight: 700; color: var(--ct-accent);">
                    ${formatAngka(item.poin)}
                </td>
                <td data-label="Streak" class="angka" style="text-align: right;">
                    🔥 ${item.streak} hari
                </td>
                <td data-label="Penurunan" class="angka" style="text-align: right; color: var(--ct-primary);">
                    ${penurunanText}
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Perbarui baris lengket saya di bawah layar
        if (json.saya) {
            const elPeringkat = document.getElementById('saya-peringkat-angka');
            const elPoin = document.getElementById('saya-poin-angka');
            const elStreak = document.getElementById('saya-streak-angka');
            const elPenurunan = document.getElementById('saya-penurunan-angka');

            if (elPeringkat) elPeringkat.textContent = json.saya.peringkat;
            if (elPoin) elPoin.textContent = formatAngka(json.saya.poin);
            if (elStreak) elStreak.textContent = `🔥 ${json.saya.streak} hari`;
            if (elPenurunan) elPenurunan.textContent = json.saya.penurunan > 0 ? `${formatAngka(json.saya.penurunan)}%` : '-';
        }
    }
</script>
@endsection
