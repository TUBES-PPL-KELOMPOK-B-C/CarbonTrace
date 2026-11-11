@props([
    'id',
    'judul' => '',
    'bahaya' => false,
])

<div id="{{ $id }}" class="ct-latar-belakang hidden" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-judul" onclick="if(event.target === this) { this.classList.add('hidden'); this.style.display = 'none'; }">
    <div class="ct-modal" style="position: relative;" onclick="event.stopPropagation();">
        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: var(--ct-s3); margin-bottom: var(--ct-s4); border-bottom: 1px solid var(--ct-border);">
            <h3 id="{{ $id }}-judul" style="font-size: 18px; font-weight: 700; margin: 0; color: {{ $bahaya ? 'var(--ct-danger)' : 'var(--ct-text)' }};">
                {{ $judul }}
            </h3>
            <button type="button" onclick="const m = document.getElementById('{{ $id }}'); if(m) { m.classList.add('hidden'); m.style.display = 'none'; }" style="background: none; border: none; cursor: pointer; color: var(--ct-text-muted); padding: 4px; display: flex; align-items: center; justify-content: center; border-radius: 6px;" aria-label="Tutup dialog">
                <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div>
            {{ $slot }}
        </div>

        @isset($tombol)
            <div style="display: flex; justify-content: flex-end; gap: var(--ct-s3); padding-top: var(--ct-s3); margin-top: var(--ct-s4); border-top: 1px solid var(--ct-border);">
                {{ $tombol }}
            </div>
        @endisset
    </div>
</div>
