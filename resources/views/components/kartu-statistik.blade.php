@props([
    'label' => '',
    'angka' => '0',
    'satuan' => '',
    'tren' => null, // ['persen' => 12, 'naik' => false]
])

<div {{ $attributes->merge(['class' => 'ct-kartu p-5']) }}>
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium" style="color: var(--ct-text-muted);">{{ $label }}</span>
        @isset($ikon)
            <div class="p-2 rounded-lg" style="background: var(--ct-surface-raised); color: var(--ct-primary);">
                {{ $ikon }}
            </div>
        @endisset
    </div>

    <div class="flex items-baseline gap-2">
        <span class="text-3xl font-extrabold" style="color: var(--ct-text);" aria-live="polite">
            {{ $angka }}
        </span>
        @if($satuan)
            <span class="text-sm font-medium" style="color: var(--ct-text-muted);">{{ $satuan }}</span>
        @endif
    </div>

    @if($tren)
        <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold {{ $tren['naik'] ? 'text-rose-400' : 'text-emerald-400' }}">
            @if($tren['naik'])
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
            @else
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            @endif
            <span>{{ $tren['persen'] }}% dibanding minggu lalu</span>
        </div>
    @endif
</div>
