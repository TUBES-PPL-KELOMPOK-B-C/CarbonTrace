@props([
    'varian' => 'aman', // aman, hampir, terlampaui, netral
])

@php
    $kelasVarian = match($varian) {
        'hampir' => 'ct-pelat--hampir',
        'terlampaui' => 'ct-pelat--terlampaui',
        'netral' => 'ct-pelat--netral',
        default => 'ct-pelat--aman',
    };
@endphp

<span {{ $attributes->merge(['class' => "ct-pelat {$kelasVarian}"]) }}>
    {{ $slot }}
</span>
