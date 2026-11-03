@props([
    'id',
    'judul' => '',
    'lebar' => '480px',
])

<div id="{{ $id }}-backdrop" class="ct-latar-belakang hidden" onclick="if(event.target === this) document.getElementById('{{ $id }}').classList.add('translate-x-full'); setTimeout(() => this.classList.add('hidden'), 200);">
    <div id="{{ $id }}" class="ct-panel-geser translate-x-full transition-transform duration-200" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-judul" style="max-width: {{ $lebar }};">
        <div class="flex items-center justify-between p-5 border-b" style="border-color: var(--ct-border); background: var(--ct-surface);">
            <h3 id="{{ $id }}-judul" class="text-lg font-bold" style="color: var(--ct-text);">{{ $judul }}</h3>
            <button type="button" onclick="document.getElementById('{{ $id }}').classList.add('translate-x-full'); setTimeout(() => document.getElementById('{{ $id }}-backdrop').classList.add('hidden'), 200);" class="p-1.5 rounded-lg hover:bg-neutral-800" aria-label="Tutup panel" style="color: var(--ct-text-muted);">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-5 flex-1 overflow-y-auto">
            {{ $slot }}
        </div>
    </div>
</div>
