@extends('layouts.app-221')

@section('title', 'Riwayat Aktivitas Emisi')
@section('page_title', 'Riwayat Aktivitas Emisi')

@section('content')
<div class="space-y-6">

    <!-- Bilah Ringkasan & Aksi Cepat -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-kartu-statistik
            label="Total Emisi Terpilih"
            :angka="number_format($totalEmisiRentang, 2, ',', '.')"
            satuan="kg CO2e"
        >
            <x-slot:ikon>
                <svg class="w-5 h-5 text-[#10B981]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:ikon>
        </x-kartu-statistik>

        <x-kartu-statistik
            label="Jumlah Catatan"
            :angka="number_format($aktivitas->total(), 0, ',', '.')"
            satuan="aktivitas"
        >
            <x-slot:ikon>
                <svg class="w-5 h-5 text-[#38BDF8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </x-slot:ikon>
        </x-kartu-statistik>

        <div class="ct-kartu p-5 flex flex-col justify-between">
            <span class="text-sm font-medium text-[#8CA099]">Aksi Cepat</span>
            <div class="flex gap-2 mt-2">
                <button type="button" onclick="bukaPanelCatat()" class="ct-tombol ct-tombol--utama flex-1 !h-11 text-sm font-semibold">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Catat Aktivitas
                </button>
                <a href="{{ route('template.index') }}" class="ct-tombol ct-tombol--sekunder !h-11 text-sm font-semibold px-4" title="Gunakan Template Rutin">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Riwayat -->
    <x-kartu>
        <form method="GET" action="{{ route('aktivitas.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="ct-isian-grup">
                <label for="filter-kategori" class="ct-label">Kategori</label>
                <select name="kategori_id" id="filter-kategori" class="ct-isian">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat->id }}" @selected(request('kategori_id') == $kat->id)>
                            {{ $kat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ct-isian-grup">
                <label for="filter-mulai" class="ct-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" id="filter-mulai" class="ct-isian" value="{{ request('tanggal_mulai') }}">
            </div>

            <div class="ct-isian-grup">
                <label for="filter-selesai" class="ct-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" id="filter-selesai" class="ct-isian" value="{{ request('tanggal_selesai') }}">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="ct-tombol ct-tombol--utama flex-1 !h-[48px]">
                    Terapkan
                </button>
                @if(request()->hasAny(['kategori_id', 'tanggal_mulai', 'tanggal_selesai']))
                    <a href="{{ route('aktivitas.index') }}" class="ct-tombol ct-tombol--sekunder !h-[48px] px-4" title="Hapus Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </x-kartu>

    <!-- Tabel Riwayat Aktivitas -->
    <x-kartu>
        <x-slot:header>
            <span class="text-xs text-[#8CA099]">Menampilkan {{ $aktivitas->count() }} dari {{ $aktivitas->total() }} catatan</span>
        </x-slot:header>

        @if($aktivitas->isEmpty())
            <x-kosong
                pesan="Belum ada catatan. Mulai dari perjalananmu hari ini."
                subpesan="Catat aktivitas harian seperti berkendara atau pemakaian listrik untuk melihat estimasi emisinya."
            >
                <x-slot:aksi>
                    <button type="button" onclick="bukaPanelCatat()" class="ct-tombol ct-tombol--utama">
                        Catat Aktivitas
                    </button>
                </x-slot:aksi>
            </x-kosong>
        @else
            <x-tabel :kepala="['Tanggal', 'Kategori', 'Aktivitas & Jumlah', 'Emisi Dihasilkan', 'Catatan', 'Aksi']">
                @foreach($aktivitas as $item)
                    <tr class="hover:bg-[#1A2622]/40 transition-colors">
                        <td data-label="Tanggal" class="text-sm text-[#E8EDEB] whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($item->activity_date)->locale('id')->translatedFormat('d F Y') }}
                        </td>
                        <td data-label="Kategori">
                            <x-pelat varian="aman">
                                {{ $item->emissionFactor->category->name ?? 'Lainnya' }}
                            </x-pelat>
                        </td>
                        <td data-label="Aktivitas & Jumlah" class="font-medium text-[#E8EDEB]">
                            <div>{{ $item->emissionFactor->name ?? 'Aktivitas' }}</div>
                            <span class="text-xs text-[#8CA099]">
                                {{ str_replace('.', ',', (string) round((float) $item->amount, 2)) }} {{ $item->emissionFactor->unit ?? '' }}
                            </span>
                        </td>
                        <td data-label="Emisi Dihasilkan" class="angka text-base font-bold text-[#A3E635]">
                            {{ str_replace('.', ',', (string) round((float) $item->emission, 2)) }} <span class="text-xs font-normal text-[#8CA099]">kg CO2e</span>
                        </td>
                        <td data-label="Catatan" class="text-xs text-[#8CA099] max-w-[200px] truncate">
                            {{ $item->note ?: '—' }}
                        </td>
                        <td data-label="Aksi" class="text-right whitespace-nowrap">
                            <div class="ct-menu-titik-tiga">
                                <button type="button" onclick="toggleMenu('menu-{{ $item->id }}')" class="p-2 rounded-lg hover:bg-[#1A2622] text-[#8CA099] hover:text-white" aria-label="Menu aksi catatan">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                                <div id="menu-{{ $item->id }}" class="ct-menu-titik-tiga__dropdown hidden">
                                    <a href="{{ route('aktivitas.edit', $item) }}" class="ct-menu-titik-tiga__item">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Ubah
                                    </a>
                                    <button type="button" onclick="konfirmasiHapus('{{ $item->id }}', '{{ $item->emissionFactor->name ?? 'Aktivitas' }}', '{{ str_replace('.', ',', (string) round((float) $item->emission, 2)) }}')" class="ct-menu-titik-tiga__item ct-bahaya text-rose-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-tabel>

            <div class="mt-4">
                {{ $aktivitas->links() }}
            </div>
        @endif
    </x-kartu>

</div>

<!-- Panel Geser: Catat Aktivitas (PBI-04 Form) -->
<x-panel-geser id="panel-catat-aktivitas" judul="Catat Aktivitas Harian" lebar="520px">
    <form id="form-catat" method="POST" action="{{ route('aktivitas.store') }}" class="space-y-5">
        @csrf

        <!-- 1. Pemilih Kategori (4 Kartu Berikon) -->
        <div>
            <label class="ct-label mb-2 block">Pilih Kategori <span class="text-rose-500">*</span></label>
            <div class="ct-pilihan-kartu" role="radiogroup" aria-label="Kategori emisi">
                @foreach($kategoriList as $index => $kat)
                    <div class="ct-kartu-pilihan {{ $index === 0 ? 'ct-aktif' : '' }}"
                         tabindex="0"
                         role="radio"
                         aria-checked="{{ $index === 0 ? 'true' : 'false' }}"
                         onclick="pilihKategori({{ $kat->id }}, this)"
                         onkeydown="if(event.key === ' ' || event.key === 'Enter') { pilihKategori({{ $kat->id }}, this); event.preventDefault(); }">
                        <div class="text-xl mb-1">
                            @if(str_contains(strtolower($kat->name), 'trans')) 🚗
                            @elseif(str_contains(strtolower($kat->name), 'listrik')) ⚡
                            @elseif(str_contains(strtolower($kat->name), 'makan')) 🍱
                            @else ♻️
                            @endif
                        </div>
                        <span class="text-xs font-semibold block text-[#E8EDEB]">{{ $kat->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2. Pemilih Faktor Emisi (Menyesuaikan Kategori) -->
        <div class="ct-isian-grup">
            <label for="pemilih-faktor" class="ct-label">Jenis Aktivitas (Faktor Emisi) <span class="text-rose-500">*</span></label>
            <select name="emission_factor_id" id="pemilih-faktor" class="ct-isian" required onchange="perbaruiSatuanDanPratinjau()">
                <option value="" disabled selected>Pilih jenis aktivitas...</option>
                @foreach($faktorEmisiList as $factor)
                    <option value="{{ $factor->id }}"
                            data-kategori="{{ $factor->category_id }}"
                            data-unit="{{ $factor->unit }}"
                            data-nilai="{{ $factor->factor_value }}">
                        {{ $factor->name }} ({{ str_replace('.', ',', (string) $factor->factor_value) }} kg CO2e/{{ $factor->unit }})
                    </option>
                @endforeach
            </select>
            @error('emission_factor_id')
                <span class="ct-galat" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- 3. Kolom Jumlah dengan Satuan Menempel -->
        <div class="ct-isian-grup">
            <label for="input-jumlah" class="ct-label">Jumlah Aktivitas <span class="text-rose-500">*</span></label>
            <div class="ct-isian-satuan">
                <input type="number" step="any" min="0.01" max="100000" name="amount" id="input-jumlah" class="ct-isian" placeholder="0" required oninput="debouncePratinjau()">
                <span id="label-satuan">satuan</span>
            </div>
            @error('amount')
                <span class="ct-galat" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- 4. Pemilih Tanggal (Maksimal Hari Ini) -->
        <div class="ct-isian-grup">
            <label for="input-tanggal" class="ct-label">Tanggal Aktivitas <span class="text-rose-500">*</span></label>
            <input type="date" name="activity_date" id="input-tanggal" class="ct-isian" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
            @error('activity_date')
                <span class="ct-galat" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- 5. Catatan Opsional -->
        <div class="ct-isian-grup">
            <label for="input-catatan" class="ct-label">Catatan Tambahan (Opsional)</label>
            <input type="text" name="note" id="input-catatan" class="ct-isian" maxlength="255" placeholder="Contoh: Pergi belanja bulanan">
            @error('note')
                <span class="ct-galat" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- 6. Kotak Pratinjau Emisi (Aria-Live) -->
        <div class="ct-pratinjau-hitung" aria-live="polite" id="kotak-pratinjau">
            <div class="text-xs text-[#8CA099] mb-1 font-medium">Estimasi Emisi yang Dihasilkan</div>
            <div id="pratinjau-angka" class="ct-angka">0,00 <span class="text-sm font-normal text-[#8CA099]">kg CO2e</span></div>
            <div id="pratinjau-rincian" class="text-xs text-[#8CA099] mt-1">Masukkan jumlah aktivitas untuk menghitung</div>
        </div>

        <!-- 7. Opsi Simpan Sebagai Template -->
        <div class="p-3.5 rounded-xl border border-[#1E2B26] bg-[#111A17] space-y-3">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="simpan_template" id="cb-template" value="1" class="rounded border-[#1E2B26] text-emerald-500 focus:ring-emerald-500" onchange="toggleNamaTemplate(this)">
                <span class="text-sm font-medium text-[#E8EDEB]">Simpan sebagai Template Rutin</span>
            </label>
            <div id="wadah-nama-template" class="hidden">
                <input type="text" name="nama_template" id="input-nama-template" class="ct-isian text-sm !h-10" placeholder="Nama template, misal: Motor ke Kampus">
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="pt-3">
            <button type="submit" id="tombol-simpan" class="ct-tombol ct-tombol--utama w-full">
                Simpan Catatan Aktivitas
            </button>
        </div>
    </form>
</x-panel-geser>

<!-- Dialog Konfirmasi Hapus Modal -->
<x-modal id="modal-hapus" judul="Hapus Catatan Aktivitas" :bahaya="true">
    <p class="text-sm text-[#8CA099] mb-4">
        Apakah kamu yakin ingin menghapus catatan <span id="nama-hapus" class="font-bold text-white"></span> sebesar <span id="emisi-hapus" class="font-bold text-[#A3E635]"></span> kg CO2e?
    </p>
    <form id="form-hapus" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('modal-hapus').classList.add('hidden')" class="ct-tombol ct-tombol--sekunder !h-10 text-sm">
                Batal
            </button>
            <button type="submit" class="ct-tombol ct-tombol--bahaya !h-10 text-sm">
                Hapus Catatan
            </button>
        </div>
    </form>
</x-modal>

@endsection

@push('scripts')
<script>
    let timerDebounce = null;

    function bukaPanelCatat() {
        const backdrop = document.getElementById('panel-catat-aktivitas-backdrop');
        const panel = document.getElementById('panel-catat-aktivitas');
        backdrop.classList.remove('hidden');
        setTimeout(() => panel.classList.remove('translate-x-full'), 10);

        // Pilih kategori pertama
        const kategoriAwal = document.querySelector('.ct-kartu-pilihan');
        if (kategoriAwal) kategoriAwal.click();
    }

    function pilihKategori(kategoriId, element) {
        document.querySelectorAll('.ct-kartu-pilihan').forEach(el => {
            el.classList.remove('ct-aktif');
            el.setAttribute('aria-checked', 'false');
        });
        element.classList.add('ct-aktif');
        element.setAttribute('aria-checked', 'true');

        // Saring opsi faktor emisi
        const select = document.getElementById('pemilih-faktor');
        let opsiPertama = null;

        Array.from(select.options).forEach(opt => {
            if (!opt.value) return;
            const match = opt.getAttribute('data-kategori') == kategoriId;
            opt.style.display = match ? 'block' : 'none';
            if (match && !opsiPertama) opsiPertama = opt;
        });

        if (opsiPertama) {
            select.value = opsiPertama.value;
            perbaruiSatuanDanPratinjau();
        }
    }

    function perbaruiSatuanDanPratinjau() {
        const select = document.getElementById('pemilih-faktor');
        const selectedOpt = select.options[select.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            const unit = selectedOpt.getAttribute('data-unit') || 'satuan';
            document.getElementById('label-satuan').textContent = unit;
            hitungPratinjau();
        }
    }

    function debouncePratinjau() {
        clearTimeout(timerDebounce);
        // Tampilkan 3 titik berdenyut saat sedang memuat
        document.getElementById('pratinjau-angka').innerHTML = `
            <span class="inline-flex items-center gap-1 text-emerald-400">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span class="text-sm">Menghitung...</span>
            </span>
        `;
        timerDebounce = setTimeout(hitungPratinjau, 300);
    }

    function hitungPratinjau() {
        const factorId = document.getElementById('pemilih-faktor').value;
        const amount = document.getElementById('input-jumlah').value;

        if (!factorId || !amount || parseFloat(amount) <= 0) {
            document.getElementById('pratinjau-angka').innerHTML = `0,00 <span class="text-sm font-normal text-[#8CA099]">kg CO2e</span>`;
            document.getElementById('pratinjau-rincian').textContent = 'Masukkan jumlah aktivitas untuk menghitung';
            return;
        }

        fetch('{{ route('aktivitas.pratinjau') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                emission_factor_id: factorId,
                amount: amount
            })
        })
        .then(res => {
            if (!res.ok) throw res;
            return res.json();
        })
        .then(data => {
            const angkaFormatted = String(data.emisi).replace('.', ',');
            document.getElementById('pratinjau-angka').innerHTML = `${angkaFormatted} <span class="text-sm font-normal text-[#8CA099]">${data.satuan}</span>`;
            document.getElementById('pratinjau-rincian').textContent = data.rincian;
        })
        .catch(async err => {
            let msg = 'Galat dalam perhitungan';
            try {
                const json = await err.json();
                if (json.message) msg = json.message;
            } catch(e){}
            document.getElementById('pratinjau-angka').innerHTML = `<span class="text-sm text-rose-400">${msg}</span>`;
            document.getElementById('pratinjau-rincian').textContent = 'Periksa kembali isian formulir';
        });
    }

    function toggleNamaTemplate(cb) {
        document.getElementById('wadah-nama-template').classList.toggle('hidden', !cb.checked);
        if (cb.checked) {
            document.getElementById('input-nama-template').focus();
        }
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

    function konfirmasiHapus(id, nama, emisi) {
        document.getElementById('nama-hapus').textContent = nama;
        document.getElementById('emisi-hapus').textContent = emisi;
        document.getElementById('form-hapus').action = `/aktivitas/${id}`;
        document.getElementById('modal-hapus').classList.remove('hidden');
    }

    @if($bukaFormulir || $errors->any())
        document.addEventListener('DOMContentLoaded', bukaPanelCatat);
    @endif
</script>
@endpush
