@props([
    'jenis' => 'sukses', // sukses, galat, info
    'pesan' => null,
])

@php
    $pesan = $pesan ?? session('sukses') ?? session('success') ?? session('galat') ?? session('error') ?? session('info');
    if (!$pesan && session('status')) {
        $pesan = session('status');
    }
    $jenisAktif = session('galat') || session('error') ? 'galat' : (session('info') ? 'info' : $jenis);
@endphp

@if($pesan)
    <div x-data="{ tampil: true }" x-show="tampil" x-init="setTimeout(() => tampil = false, 4000)" id="ct-toast" class="ct-notifikasi" role="status" aria-live="polite">
        @if($jenisAktif === 'galat')
            <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif($jenisAktif === 'info')
            <svg class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @else
            <svg class="w-5 h-5" style="color: var(--ct-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        @endif
        <span class="text-sm font-medium">{{ $pesan }}</span>
        <button type="button" onclick="this.parentElement.remove()" class="ml-2 opacity-60 hover:opacity-100" aria-label="Tutup notifikasi">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <script>
        setTimeout(() => {
            const el = document.getElementById('ct-toast');
            if (el) el.remove();
        }, 4000);
    </script>
@endif
