@extends('layouts.app-221')

@section('title', 'Catat Aktivitas Emisi')
@section('page_title', 'Catat Aktivitas Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('aktivitas.index') }}" class="inline-flex items-center gap-2 text-sm text-[#8CA099] hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Riwayat
        </a>
    </div>

    <x-kartu judul="Formulir Catat Aktivitas">
        <form method="POST" action="{{ route('aktivitas.store') }}" class="space-y-5">
            @csrf

            <!-- Kategori -->
            <div>
                <label class="ct-label mb-2 block">Pilih Kategori <span class="text-rose-500">*</span></label>
                <div class="ct-pilihan-kartu" role="radiogroup" aria-label="Kategori emisi">
                    @foreach($kategoriList as $index => $kat)
                        <div class="ct-kartu-pilihan {{ $index === 0 ? 'ct-aktif' : '' }}"
                             tabindex="0"
                             role="radio"
                             aria-checked="{{ $index === 0 ? 'true' : 'false' }}"
                             onclick="pilihKategori({{ $kat->id }}, this)">
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

            <!-- Faktor Emisi -->
            <div class="ct-isian-grup">
                <label for="pemilih-faktor" class="ct-label">Jenis Aktivitas <span class="text-rose-500">*</span></label>
                <select name="emission_factor_id" id="pemilih-faktor" class="ct-isian" required onchange="perbaruiSatuanDanPratinjau()">
                    <option value="" disabled selected>Pilih jenis aktivitas...</option>
                    @foreach($faktorEmisiList as $factor)
                        <option value="{{ $factor->id }}"
                                data-kategori="{{ $factor->category_id }}"
                                data-unit="{{ $factor->unit }}"
                                data-nilai="{{ $factor->factor_value }}"
                                @selected(old('emission_factor_id') == $factor->id)>
                            {{ $factor->name }} ({{ str_replace('.', ',', (string) $factor->factor_value) }} kg CO2e/{{ $factor->unit }})
                        </option>
                    @endforeach
                </select>
                @error('emission_factor_id')
                    <span class="ct-galat">{{ $message }}</span>
                @enderror
            </div>

            <!-- Jumlah -->
            <div class="ct-isian-grup">
                <label for="input-jumlah" class="ct-label">Jumlah Aktivitas <span class="text-rose-500">*</span></label>
                <div class="ct-isian-satuan">
                    <input type="number" step="any" min="0.01" max="100000" name="amount" id="input-jumlah" class="ct-isian" value="{{ old('amount') }}" placeholder="0" required oninput="debouncePratinjau()">
                    <span id="label-satuan">satuan</span>
                </div>
                @error('amount')
                    <span class="ct-galat">{{ $message }}</span>
                @enderror
            </div>

            <!-- Tanggal -->
            <div class="ct-isian-grup">
                <label for="input-tanggal" class="ct-label">Tanggal Aktivitas <span class="text-rose-500">*</span></label>
                <input type="date" name="activity_date" id="input-tanggal" class="ct-isian" value="{{ old('activity_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                @error('activity_date')
                    <span class="ct-galat">{{ $message }}</span>
                @enderror
            </div>

            <!-- Catatan -->
            <div class="ct-isian-grup">
                <label for="input-catatan" class="ct-label">Catatan Tambahan (Opsional)</label>
                <input type="text" name="note" id="input-catatan" class="ct-isian" value="{{ old('note') }}" maxlength="255" placeholder="Contoh: Pergi belanja">
                @error('note')
                    <span class="ct-galat">{{ $message }}</span>
                @enderror
            </div>

            <!-- Pratinjau -->
            <div class="ct-pratinjau-hitung" aria-live="polite">
                <div class="text-xs text-[#8CA099] mb-1 font-medium">Estimasi Emisi yang Dihasilkan</div>
                <div id="pratinjau-angka" class="ct-angka">0,00 <span class="text-sm font-normal text-[#8CA099]">kg CO2e</span></div>
                <div id="pratinjau-rincian" class="text-xs text-[#8CA099] mt-1">Masukkan jumlah untuk menghitung</div>
            </div>

            <!-- Simpan Sebagai Template -->
            <div class="p-3.5 rounded-xl border border-[#1E2B26] bg-[#111A17] space-y-3">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="simpan_template" id="cb-template" value="1" class="rounded border-[#1E2B26] text-emerald-500" onchange="document.getElementById('wadah-nama-template').classList.toggle('hidden', !this.checked)">
                    <span class="text-sm font-medium text-[#E8EDEB]">Simpan sebagai Template Rutin</span>
                </label>
                <div id="wadah-nama-template" class="hidden">
                    <input type="text" name="nama_template" class="ct-isian text-sm !h-10" placeholder="Nama template, misal: Motor ke Kampus">
                </div>
            </div>

            <div class="flex gap-3 pt-3">
                <a href="{{ route('aktivitas.index') }}" class="ct-tombol ct-tombol--sekunder flex-1">
                    Batal
                </a>
                <button type="submit" class="ct-tombol ct-tombol--utama flex-1">
                    Simpan Catatan
                </button>
            </div>
        </form>
    </x-kartu>
</div>
@endsection

@push('scripts')
<script>
    let timerDebounce = null;
    function pilihKategori(kategoriId, element) {
        document.querySelectorAll('.ct-kartu-pilihan').forEach(el => el.classList.remove('ct-aktif'));
        element.classList.add('ct-aktif');
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
            document.getElementById('label-satuan').textContent = selectedOpt.getAttribute('data-unit') || 'satuan';
            hitungPratinjau();
        }
    }

    function debouncePratinjau() {
        clearTimeout(timerDebounce);
        document.getElementById('pratinjau-angka').innerHTML = `<span class="text-sm text-emerald-400">Menghitung...</span>`;
        timerDebounce = setTimeout(hitungPratinjau, 300);
    }

    function hitungPratinjau() {
        const factorId = document.getElementById('pemilih-faktor').value;
        const amount = document.getElementById('input-jumlah').value;
        if (!factorId || !amount || parseFloat(amount) <= 0) {
            document.getElementById('pratinjau-angka').innerHTML = `0,00 <span class="text-sm font-normal text-[#8CA099]">kg CO2e</span>`;
            document.getElementById('pratinjau-rincian').textContent = 'Masukkan jumlah untuk menghitung';
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
            document.getElementById('pratinjau-angka').innerHTML = `${formatted} <span class="text-sm font-normal text-[#8CA099]">${data.satuan}</span>`;
            document.getElementById('pratinjau-rincian').textContent = data.rincian;
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const elAwal = document.querySelector('.ct-kartu-pilihan');
        if (elAwal) elAwal.click();
    });
</script>
@endpush
