@props([
    'item' => [], // [['id' => 'harian', 'label' => 'Harian'], ...]
    'aktif' => null,
])

<div {{ $attributes->merge(['class' => 'flex border-b', 'style' => 'border-color: var(--ct-border);']) }} role="tablist">
    @foreach($item as $tab)
        @php
            $isAktif = $aktif === $tab['id'];
        @endphp
        <button
            type="button"
            role="tab"
            aria-selected="{{ $isAktif ? 'true' : 'false' }}"
            class="px-4 py-3 text-sm font-semibold transition-colors duration-150 border-b-2 -mb-[1px] {{ $isAktif ? 'border-emerald-500 text-white' : 'border-transparent text-neutral-400 hover:text-white' }}"
            onclick="pilihTab('{{ $tab['id'] }}')"
        >
            {{ $tab['label'] }}
        </button>
    @endforeach
</div>
