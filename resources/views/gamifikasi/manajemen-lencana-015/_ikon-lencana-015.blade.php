{{-- PBI-13: ikon lencana disimpan sebagai nama di kolom icon, bukan berkas gambar, supaya tidak
     perlu unggahan. Nama yang tidak dikenali jatuh ke ikon lencana umum.
     Dipanggil: @include('gamifikasi.manajemen-lencana-015._ikon-lencana-015', ['nama' => $x]) --}}
@php
    $jalur = match ($nama ?? '') {
        'bintang' => '<path d="M12 3l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 18l-5.8 2.9 1.1-6.5L2.6 9.8l6.5-.9z"/>',
        'piala' => '<path d="M8 4h8v5a4 4 0 0 1-8 0zM8 6H5v2a3 3 0 0 0 3 3M16 6h3v2a3 3 0 0 1-3 3M9 20h6M12 13v7"/>',
        'mahkota' => '<path d="M3 8l3.5 3L12 5l5.5 6L21 8l-2 10H5z"/>',
        'api' => '<path d="M12 3c3 4 5 6 5 9a5 5 0 0 1-10 0c0-2 1-3.5 2.5-5"/><path d="M12 21a2.5 2.5 0 0 0 2.5-2.5c0-1.5-2.5-3.5-2.5-3.5s-2.5 2-2.5 3.5A2.5 2.5 0 0 0 12 21z"/>',
        'kalender' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'buku' => '<path d="M4 5h6a2 2 0 0 1 2 2v13a2 2 0 0 0-2-2H4zM20 5h-6a2 2 0 0 0-2 2v13a2 2 0 0 1 2-2h6z"/>',
        'kotak' => '<path d="M3 8l9-5 9 5v8l-9 5-9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
        'jejak' => '<path d="M9 4c1.7 0 2.5 1.6 2.5 3.5S10.7 12 9 12s-2.5-1.6-2.5-4.5S7.3 4 9 4zM17 10c1.3 0 2 1.2 2 2.8s-.7 3.2-2 3.2-2-1.2-2-3.2.7-2.8 2-2.8z"/><path d="M6.5 14h5v3a2.5 2.5 0 0 1-5 0zM15 18h4v2a2 2 0 0 1-4 0z"/>',
        'bumi' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14.5 0 18M12 3C9 6.5 9 17.5 12 21"/>',
        'roket' => '<path d="M12 3c3.5 2.5 5 6 5 10l-3 3h-4l-3-3c0-4 1.5-7.5 5-10z"/><circle cx="12" cy="10" r="1.6"/><path d="M9 19l-2 2M15 19l2 2"/>',
        default => '<circle cx="12" cy="12" r="8"/><path d="M12 8.5l1.3 2.6 2.9.4-2.1 2 .5 2.9L12 15l-2.6 1.4.5-2.9-2.1-2 2.9-.4z"/>',
    };
@endphp

<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $jalur !!}</svg>
