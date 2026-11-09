@extends('layouts.app-221')

@section('title', 'Template Aktivitas Rutin')
@section('page_title', 'Template Aktivitas Rutin')

@section('content')
<div class="space-y-6">

    <!-- Header & Tombol Tambah -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">Template Aktivitas Kamu</h2>
            <p class="text-sm text-[#8CA099] mt-0.5">Pintasan satu klik untuk mencatat aktivitas harian yang berulang.</p>
        </div>
        <button type="button" onclick="bukaPanelTemplate()" class="ct-tombol ct-tombol--utama text-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Template Baru
        </button>
    </div>

    <!-- Keadaan Kosong atau Kisi Kartu Template -->
    @if($templates->isEmpty())
        <x-kartu>
            <x-kosong
                pesan="Belum ada template. Simpan aktivitas yang kamu lakukan tiap hari supaya tidak perlu mengisi ulang."
                subpesan="Misalnya perjalanan harian ke kampus atau pemakaian AC semalaman."
            >
                <x-slot:aksi>
                    <button type="button" onclick="bukaPanelTemplate()" class="ct-tombol ct-tombol--utama">
                        Buat Template Pertama
                    </button>
                </x-slot:aksi>
            </x-kosong>
        </x-kartu>
    @else
        <!-- Kisi Kartu Template (4 kolom di 1440px, 2 kolom di 768px, 1 kolom di 375px) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($templates as $tmpl)
                @php
                    $factorAktif = $tmpl->emissionFactor && $tmpl->emissionFactor->is_active;
                @endphp
                <div class="ct-kartu flex flex-col justify-between relative group hover:border-emerald-500/50 transition-all duration-200">
                    <div>
                        <!-- Header Kartu: Kategori & Menu Titik Tiga -->
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <x-pelat :varian="$factorAktif ? 'aman' : 'terlampaui'">
                                @if($factorAktif)
                                    {{ $tmpl->emissionFactor->category->name ?? 'Aktivitas' }}
                                @else
                                    Perlu Diperbarui
                                @endif
                            </x-pelat>

                            <div class="ct-menu-titik-tiga">
                                <button type="button" onclick="toggleMenu('menu-tmpl-{{ $tmpl->id }}')" class="p-1 rounded-lg text-[#8CA099] hover:text-white" aria-label="Menu template {{ $tmpl->name }}">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                                <div id="menu-tmpl-{{ $tmpl->id }}" class="ct-menu-titik-tiga__dropdown hidden">
                                    <button type="button" onclick="editTemplate({{ json_encode($tmpl) }})" class="ct-menu-titik-tiga__item">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Ubah
                                    </button>
                                    <button type="button" onclick="gandakanTemplate({{ json_encode($tmpl) }})" class="ct-menu-titik-tiga__item">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        Gandakan
                                    </button>
                                    <button type="button" onclick="konfirmasiHapusTemplate('{{ $tmpl->id }}', '{{ $tmpl->name }}')" class="ct-menu-titik-tiga__item ct-bahaya text-rose-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Nama & Rincian -->
                        <h3 class="text-base font-bold text-white mb-1 truncate" title="{{ $tmpl->name }}">
                            {{ $tmpl->name }}
                        </h3>
                        <p class="text-xs text-[#8CA099] mb-4">
                            {{ $tmpl->emissionFactor->name ?? 'Faktor diarsipkan' }} • {{ str_replace('.', ',', (string) round((float) $tmpl->amount, 2)) }} {{ $tmpl->emissionFactor->unit ?? '' }}
                        </p>
                    </div>

                    <!-- Taksiran Emisi & Tombol Pakai -->
                    <div class="pt-3 border-t border-[#1E2B26] space-y-3">
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs text-[#8CA099]">Taksiran emisi:</span>
                            <span class="text-base font-extrabold text-[#A3E635]">
                                {{ str_replace('.', ',', (string) round((float) $tmpl->estimated_emission, 2)) }} <span class="text-xs font-normal text-[#8CA099]">kg CO2e</span>
                            </span>
                        </div>

                        <form method="POST" action="{{ route('template.pakai', $tmpl) }}" onsubmit="handlePakaiSubmit(this, event)">
                            @csrf
                            <button type="submit"
                                    class="tombol-pakai ct-tombol {{ $factorAktif ? 'ct-tombol--utama' : 'ct-tombol--sekunder opacity-50 cursor-not-allowed' }} w-full !h-10 text-xs font-semibold"
                                    @disabled(!$factorAktif)
                                    title="{{ $factorAktif ? 'Catat aktivitas ini untuk hari ini' : 'Faktor emisi telah diarsipkan, ubah template terlebih dahulu' }}">
                                <span class="label-teks flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Pakai Hari Ini
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<!-- Panel Geser: Buat / Ubah Template -->
<x-panel-geser id="panel-template" judul="Buat Template Aktivitas Rutin">
    <form id="form-template" method="POST" action="{{ route('template.store') }}" class="space-y-5">
        @csrf
        <div id="wadah-metode-put"></div>

        <!-- Nama Template -->
        <div class="ct-isian-grup">
            <label for="tmpl-nama" class="ct-label">Nama Template <span class="text-rose-500">*</span></label>
            <input type="text" name="name" id="tmpl-nama" class="ct-isian" maxlength="80" placeholder="Contoh: Motor ke Kampus Pulang Pergi" required>
            @error('name')
                <span class="ct-galat" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- Faktor Emisi -->
        <div class="ct-isian-grup">
            <label for="tmpl-faktor" class="ct-label">Jenis Aktivitas (Faktor Emisi) <span class="text-rose-500">*</span></label>
            <select name="emission_factor_id" id="tmpl-faktor" class="ct-isian" required onchange="perbaruiSatuanTemplate()">
                <option value="" disabled selected>Pilih jenis aktivitas...</option>
                @foreach($faktorEmisiList as $factor)
                    <option value="{{ $factor->id }}"
                            data-unit="{{ $factor->unit }}"
                            data-nilai="{{ $factor->factor_value }}">
                        [{{ $factor->category->name ?? 'Kategori' }}] {{ $factor->name }} ({{ str_replace('.', ',', (string) $factor->factor_value) }} kg CO2e/{{ $factor->unit }})
                    </option>
                @endforeach
            </select>
            @error('emission_factor_id')
                <span class="ct-galat" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- Jumlah -->
        <div class="ct-isian-grup">
            <label for="tmpl-jumlah" class="ct-label">Jumlah Aktivitas <span class="text-rose-500">*</span></label>
            <div class="ct-isian-satuan">
                <input type="number" step="any" min="0.01" max="100000" name="amount" id="tmpl-jumlah" class="ct-isian" placeholder="0" required oninput="hitungPratinjauTemplate()">
                <span id="tmpl-label-satuan">satuan</span>
            </div>
            @error('amount')
                <span class="ct-galat" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- Pratinjau Taksiran -->
        <div class="ct-pratinjau-hitung" aria-live="polite">
            <div class="text-xs text-[#8CA099] mb-1 font-medium">Taksiran Emisi per Pemakaian</div>
            <div id="tmpl-pratinjau-angka" class="ct-angka">0,00 <span class="text-sm font-normal text-[#8CA099]">kg CO2e</span></div>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-3">
            <button type="submit" id="tmpl-tombol-submit" class="ct-tombol ct-tombol--utama w-full">
                Simpan Template
            </button>
        </div>
    </form>
</x-panel-geser>

<!-- Dialog Hapus Modal -->
<x-modal id="modal-hapus-tmpl" judul="Hapus Template" :bahaya="true">
    <p class="text-sm text-[#8CA099] mb-4">
        Apakah kamu yakin ingin menghapus template <span id="nama-hapus-tmpl" class="font-bold text-white"></span>?
        <span class="block text-xs text-[#8CA099]/80 mt-1">Catatan riwayat yang pernah dibuat dari template ini tidak akan terhapus.</span>
    </p>
    <form id="form-hapus-tmpl" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('modal-hapus-tmpl').classList.add('hidden')" class="ct-tombol ct-tombol--sekunder !h-10 text-sm">
                Batal
            </button>
            <button type="submit" class="ct-tombol ct-tombol--bahaya !h-10 text-sm">
                Hapus Template
            </button>
        </div>
    </form>
</x-modal>

@endsection

@push('scripts')
<script>
    function bukaPanelTemplate() {
        document.getElementById('form-template').action = '{{ route('template.store') }}';
        document.getElementById('wadah-metode-put').innerHTML = '';
        document.getElementById('panel-template-judul').textContent = 'Buat Template Aktivitas Rutin';
        document.getElementById('tmpl-tombol-submit').textContent = 'Simpan Template';
        document.getElementById('tmpl-nama').value = '';
        document.getElementById('tmpl-jumlah').value = '';
        document.getElementById('tmpl-faktor').selectedIndex = 0;
        document.getElementById('tmpl-label-satuan').textContent = 'satuan';
        document.getElementById('tmpl-pratinjau-angka').innerHTML = `0,00 <span class="text-sm font-normal text-[#8CA099]">kg CO2e</span>`;

        const backdrop = document.getElementById('panel-template-backdrop');
        const panel = document.getElementById('panel-template');
        backdrop.classList.remove('hidden');
        setTimeout(() => panel.classList.remove('translate-x-full'), 10);
    }

    function editTemplate(tmpl) {
        document.getElementById('form-template').action = `/template-aktivitas/${tmpl.id}`;
        document.getElementById('wadah-metode-put').innerHTML = `@method('PUT')`;
        document.getElementById('panel-template-judul').textContent = 'Ubah Template Aktivitas';
        document.getElementById('tmpl-tombol-submit').textContent = 'Simpan Perubahan';

        document.getElementById('tmpl-nama').value = tmpl.name;
        document.getElementById('tmpl-jumlah').value = parseFloat(tmpl.amount);
        document.getElementById('tmpl-faktor').value = tmpl.emission_factor_id;

        perbaruiSatuanTemplate();

        const backdrop = document.getElementById('panel-template-backdrop');
        const panel = document.getElementById('panel-template');
        backdrop.classList.remove('hidden');
        setTimeout(() => panel.classList.remove('translate-x-full'), 10);
    }

    function gandakanTemplate(tmpl) {
        bukaPanelTemplate();
        document.getElementById('tmpl-nama').value = `${tmpl.name} (Salinan)`;
        document.getElementById('tmpl-jumlah').value = parseFloat(tmpl.amount);
        document.getElementById('tmpl-faktor').value = tmpl.emission_factor_id;
        perbaruiSatuanTemplate();
    }

    function perbaruiSatuanTemplate() {
        const select = document.getElementById('tmpl-faktor');
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('tmpl-label-satuan').textContent = opt.getAttribute('data-unit') || 'satuan';
            hitungPratinjauTemplate();
        }
    }

    function hitungPratinjauTemplate() {
        const factorId = document.getElementById('tmpl-faktor').value;
        const amount = document.getElementById('tmpl-jumlah').value;

        if (!factorId || !amount || parseFloat(amount) <= 0) {
            document.getElementById('tmpl-pratinjau-angka').innerHTML = `0,00 <span class="text-sm font-normal text-[#8CA099]">kg CO2e</span>`;
            return;
        }

        fetch('{{ route('aktivitas.pratinjau') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ emission_factor_id: factorId, amount: amount })
        })
        .then(res => res.json())
        .then(data => {
            const formatted = String(data.emisi).replace('.', ',');
            document.getElementById('tmpl-pratinjau-angka').innerHTML = `${formatted} <span class="text-sm font-normal text-[#8CA099]">${data.satuan}</span>`;
        });
    }

    function handlePakaiSubmit(form, e) {
        const btn = form.querySelector('.tombol-pakai');
        if (btn.disabled || btn.getAttribute('aria-busy') === 'true') {
            e.preventDefault();
            return false;
        }

        // Cegah klik ganda cepat (disable tombol)
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        btn.innerHTML = `
            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-emerald-400">Tercatat!</span>
        `;
        return true;
    }

    function toggleMenu(id) {
        const menu = document.getElementById(id);
        const isOpen = !menu.classList.contains('hidden');
        document.querySelectorAll('.ct-menu-titik-tiga__dropdown').forEach(m => m.classList.add('hidden'));
        if (!isOpen) menu.classList.remove('hidden');
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.ct-menu-titik-tiga')) {
            document.querySelectorAll('.ct-menu-titik-tiga__dropdown').forEach(m => m.classList.add('hidden'));
        }
    });

    function konfirmasiHapusTemplate(id, nama) {
        document.getElementById('nama-hapus-tmpl').textContent = nama;
        document.getElementById('form-hapus-tmpl').action = `/template-aktivitas/${id}`;
        document.getElementById('modal-hapus-tmpl').classList.remove('hidden');
    }

    @if($bukaFormulir || $errors->any())
        document.addEventListener('DOMContentLoaded', bukaPanelTemplate);
    @endif
</script>
@endpush
