{{-- Kartu statistik dashboard: label, angka besar, satuan, dan keterangan kecil (opsional).
     Diletakkan di sini, bukan di resources/views/components/, karena folder komponen bersama
     dikelola Front-End Lead; nanti tinggal diganti <x-kartu-statistik>. --}}
<article class="ds-stat">
    <h2 class="ds-stat__label">{{ $label }}</h2>
    @if ($gagal ?? false)
        <p class="ds-gagal">Gagal memuat. <a href="{{ route('dashboard.index') }}">Coba lagi</a></p>
    @else
        <p class="ds-stat__angka">{{ $angka }} <span class="ds-stat__satuan">{{ $satuan }}</span></p>
        @isset($keterangan)
            <p class="ds-stat__ket">{{ $keterangan }}</p>
        @endisset
    @endif
</article>
