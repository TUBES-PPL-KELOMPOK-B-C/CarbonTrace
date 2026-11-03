@props([
    'judul' => null,
    'subjudul' => null,
])

<div {{ $attributes->merge(['class' => 'ct-kartu']) }}>
    @if($judul || isset($header))
        <div class="flex items-center justify-between mb-4">
            <div>
                @if($judul)
                    <h3 class="ct-kartu__judul mb-0">{{ $judul }}</h3>
                @endif
                @if($subjudul)
                    <p class="text-sm text-muted mb-0" style="color: var(--ct-text-muted);">{{ $subjudul }}</p>
                @endif
            </div>
            @isset($header)
                <div>{{ $header }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</div>
