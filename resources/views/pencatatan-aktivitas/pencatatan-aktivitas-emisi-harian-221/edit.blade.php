@extends('layouts.app-221')

@section('title', 'Ubah Catatan Aktivitas')
@section('page_title', 'Ubah Catatan Aktivitas')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="mb-4">
        <a href="{{ route('aktivitas.index') }}" class="inline-flex items-center gap-2 text-sm text-[#8CA099] hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Riwayat
        </a>
    </div>

    <!-- Perbandingan Sebelum vs Sesudah -->
    <div class="grid grid-cols-2 gap-4">
        <div class="ct-kartu p-4 text-center border-neutral-700 bg-[#111A17]/60">
            <div class="text-xs text-[#8CA099] font-medium">Emisi Tercatat Sebelumnya</div>
            <div class="text-2xl font-bold text-[#8CA099] mt-1">
                {{ str_replace('.', ',', (string) round((float) $aktivitas->emission, 2)) }} <span class="text-xs">kg CO2e</span>
            </div>
            <div class="text-xs text-[#8CA099]/80 mt-1">
                {{ str_replace('.', ',', (string) round((float) $aktivitas->amount, 2)) }} {{ $aktivitas->emissionFactor->unit ?? '' }} ({{ $aktivitas->emissionFactor->name ?? '' }})
            </div>
        </div>

        <div class="ct-kartu p-4 text-center border-emerald-500/40 bg-emerald-950/20">
            <div class="text-xs text-emerald-400 font-medium">Taksiran Emisi Baru</div>
            <div id="pratinjau-angka-baru" class="text-2xl font-extrabold text-[#A3E635] mt-1" aria-live="polite">
                {{ str_replace('.', ',', (string) round((float) $aktivitas->emission, 2)) }} <span class="text-xs font-normal text-[#8CA099]">kg CO2e</span>
            </div>
            <div id="pratinjau-rincian-baru" class="text-xs text-[#8CA099] mt-1">Sesuai data isian saat ini</div>
        </div>
    </div>

    <x-kartu judul="Sunting Isian Aktivitas">
        <form method="POST" action="{{ route('aktivitas.update', $aktivitas) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Faktor Emisi -->
            <div class="ct-isian-grup">
                <label for="pemilih-faktor" class="ct-label">Jenis Aktivitas (Faktor Emisi) <span class="text-rose-500">*</span></label>
                <select name="emission_factor_id" id="pemilih-faktor" class="ct-isian" required onchange="perbaruiSatuanDanPratinjau()">
                    @foreach($faktorEmisiList as $factor)
                        <option value="{{ $factor->id }}"
                                data-unit="{{ $factor->unit }}"
                                data-nilai="{{ $factor->factor_value }}"
                                @selected(old('emission_factor_id', $aktivitas->emission_factor_id) == $factor->id)>
                            [{{ $factor->category->name ?? 'Kategori' }}] {{ $factor->name }} ({{ str_replace('.', ',', (string) $factor->factor_value) }} kg CO2e/{{ $factor->unit }})
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
                    <input type="number" step="any" min="0.01" max="100000" name="amount" id="input-jumlah" class="ct-isian" value="{{ old('amount', round((float)$aktivitas->amount, 2)) }}" required oninput="debouncePratinjau()">
                    <span id="label-satuan">{{ $aktivitas->emissionFactor->unit ?? 'satuan' }}</span>
                </div>
                @error('amount')
                    <span class="ct-galat">{{ $message }}</span>
                @enderror
            </div>

            <!-- Tanggal -->
            <div class="ct-isian-grup">
                <label for="input-tanggal" class="ct-label">Tanggal Aktivitas <span class="text-rose-500">*</span></label>
                <input type="date" name="activity_date" id="input-tanggal" class="ct-isian" value="{{ old('activity_date', $aktivitas->activity_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                @error('activity_date')
                    <span class="ct-galat">{{ $message }}</span>
                @enderror
            </div>

            <!-- Catatan -->
            <div class="ct-isian-grup">
                <label for="input-catatan" class="ct-label">Catatan Tambahan (Opsional)</label>
                <input type="text" name="note" id="input-catatan" class="ct-isian" value="{{ old('note', $aktivitas->note) }}" maxlength="255">
                @error('note')
                    <span class="ct-galat">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex gap-3 pt-3">
                <a href="{{ route('aktivitas.index') }}" class="ct-tombol ct-tombol--sekunder flex-1">
                    Batal
                </a>
                <button type="submit" class="ct-tombol ct-tombol--utama flex-1">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-kartu>
</div>
@endsection

@push('scripts')
<script>
    let timerDebounce = null;

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
        document.getElementById('pratinjau-angka-baru').innerHTML = `<span class="text-sm text-emerald-400">Menghitung...</span>`;
        timerDebounce = setTimeout(hitungPratinjau, 300);
    }

    function hitungPratinjau() {
        const factorId = document.getElementById('pemilih-faktor').value;
        const amount = document.getElementById('input-jumlah').value;

        if (!factorId || !amount || parseFloat(amount) <= 0) {
            document.getElementById('pratinjau-angka-baru').innerHTML = `0,00 <span class="text-sm font-normal text-[#8CA099]">kg CO2e</span>`;
            document.getElementById('pratinjau-rincian-baru').textContent = 'Masukkan jumlah untuk menghitung';
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
            document.getElementById('pratinjau-angka-baru').innerHTML = `${formatted} <span class="text-sm font-normal text-[#8CA099]">${data.satuan}</span>`;
            document.getElementById('pratinjau-rincian-baru').textContent = data.rincian;
        });
    }
</script>
@endpush
