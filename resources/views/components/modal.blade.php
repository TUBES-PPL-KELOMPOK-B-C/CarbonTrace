@props([
    'id',
    'judul' => '',
    'bahaya' => false,
])

<div id="{{ $id }}" class="ct-latar-belakang hidden" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-judul">
    <div class="ct-modal">
        <div class="flex items-center justify-between pb-3 mb-4 border-b" style="border-color: var(--ct-border);">
            <h4 id="{{ $id }}-judul" class="text-lg font-bold" style="color: {{ $bahaya ? 'var(--ct-danger)' : 'var(--ct-text)' }};">
                {{ $judul }}
            </h4>
            <button type="button" onclick="document.getElementById('{{ $id }}').classList.add('hidden')" class="text-muted hover:text-white" aria-label="Tutup dialog" style="color: var(--ct-text-muted);">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="mb-6">
            {{ $slot }}
        </div>

        @isset($tombol)
            <div class="flex justify-end gap-3 pt-3 border-t" style="border-color: var(--ct-border);">
                {{ $tombol }}
            </div>
        @endisset
    </div>
</div>
