@props([
    'varian' => 'utama', // utama, sekunder, hantu, bahaya
    'memuat' => false,
    'tipe' => 'button',
    'href' => null,
])

@php
    $kelasVarian = match($varian) {
        'sekunder' => 'ct-tombol--sekunder',
        'hantu' => 'ct-tombol--hantu',
        'bahaya' => 'ct-tombol--bahaya',
        default => 'ct-tombol--utama',
    };
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "ct-tombol {$kelasVarian}"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $tipe }}" {{ $attributes->merge(['class' => "ct-tombol {$kelasVarian}", 'aria-busy' => $memuat ? 'true' : 'false']) }} @disabled($memuat)>
        @if($memuat)
            <span class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span>Memuat...</span>
            </span>
        @else
            {{ $slot }}
        @endif
    </button>
@endif
