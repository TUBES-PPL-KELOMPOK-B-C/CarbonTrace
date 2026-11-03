@props([
    'ikon' => null,
    'pesan' => 'Belum ada data',
    'subpesan' => null,
])

<div {{ $attributes->merge(['class' => 'ct-kosong']) }}>
    <div class="ct-kosong__ikon flex justify-center">
        @if($ikon)
            {{ $ikon }}
        @else
            <svg class="w-16 h-16 mx-auto stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        @endif
    </div>
    
    <p class="ct-kosong__teks text-base font-medium">{{ $pesan }}</p>
    
    @if($subpesan)
        <p class="text-sm -mt-3 mb-5" style="color: var(--ct-text-muted);">{{ $subpesan }}</p>
    @endif

    @if(isset($aksi))
        <div class="mt-4 flex justify-center">
            {{ $aksi }}
        </div>
    @endif
</div>
