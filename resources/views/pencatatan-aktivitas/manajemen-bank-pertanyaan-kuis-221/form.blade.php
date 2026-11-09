@extends('layouts.app-221')

@section('title', $judul)
@section('page_title', $judul)

@section('content')
<div class="space-y-6">

    <div class="mb-2">
        <a href="{{ route('kuis.index') }}" class="inline-flex items-center gap-2 text-sm text-[#8CA099] hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Bank Pertanyaan
        </a>
    </div>

    <!-- Tata Letak 2 Kolom (Formulir di Kiri, Pratinjau Kuis di Kanan pada 1440px) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Kolom Kiri: Formulir Penyusunan Soal (7 Kolom) -->
        <div class="lg:col-span-7 space-y-6">
            <x-kartu :judul="$judul">
                <form id="form-soal" method="POST" action="{{ $aksi }}" class="space-y-5">
                    @csrf
                    @if($metode === 'PUT')
                        @method('PUT')
                    @endif

                    <!-- 1. Teks Pertanyaan (10 - 500 karakter) -->
                    <div class="ct-isian-grup">
                        <label for="input-pertanyaan" class="ct-label">
                            Teks Pertanyaan Kuis <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="question"
                                  id="input-pertanyaan"
                                  rows="3"
                                  class="ct-isian !h-auto py-3 leading-relaxed"
                                  minlength="10"
                                  maxlength="500"
                                  placeholder="Contoh: Bagaimana caramu bepergian sehari-hari ke kampus atau tempat kerja?"
                                  required
                                  oninput="perbaruiPratinjau()">{{ old('question', $soal->question) }}</textarea>
                        <div class="flex justify-between items-center text-xs text-[#8CA099]">
                            <span>Minimal 10 karakter, maksimal 500 karakter.</span>
                            <span id="hitung-karakter">0/500</span>
                        </div>
                        @error('question')
                            <span class="ct-galat" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- 2. Bobot Emisi Pertanyaan (0 - 1000) -->
                    <div class="ct-isian-grup">
                        <label for="input-emission-weight" class="ct-label">
                            Bobot Emisi Pertanyaan (Pengali Garis Dasar) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number"
                               step="0.0001"
                               min="0"
                               max="1000"
                               name="emission_weight"
                               id="input-emission-weight"
                               class="ct-isian"
                               value="{{ old('emission_weight', round((float)$soal->emission_weight, 4) ?: '1.0000') }}"
                               placeholder="1.0000"
                               required
                               oninput="perbaruiPratinjau()">
                        <span class="text-xs text-[#8CA099]">Nilai bobot pengali emisi pertanyaan ini (rentang 0 – 1.000).</span>
                        @error('emission_weight')
                            <span class="ct-galat" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- 3. Pilihan Jawaban (2 sampai 6 pilihan) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="ct-label block">
                                Pilihan Jawaban & Bobot Nilai (2 – 6 Pilihan) <span class="text-rose-500">*</span>
                            </label>
                            <button type="button"
                                    id="tombol-tambah-opsi"
                                    onclick="tambahOpsi()"
                                    class="text-xs font-semibold text-[#10B981] hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Pilihan
                            </button>
                        </div>

                        <div id="wadah-pilihan" class="space-y-3">
                            @php
                                $opsiData = old('options', $soal->options ?? [
                                    ['label' => '', 'weight' => 0],
                                    ['label' => '', 'weight' => 0],
                                ]);
                            @endphp

                            @foreach($opsiData as $idx => $opt)
                                <div class="baris-opsi flex items-center gap-2 p-3 rounded-xl border border-[#1E2B26] bg-[#111A17] transition-all" data-index="{{ $idx }}">
                                    <!-- Tombol Geser / Urutan -->
                                    <div class="flex flex-col gap-0.5">
                                        <button type="button" onclick="geserOpsi(this, -1)" class="p-1 hover:bg-[#1A2622] rounded text-[#8CA099] hover:text-white" title="Pindah ke atas">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        </button>
                                        <button type="button" onclick="geserOpsi(this, 1)" class="p-1 hover:bg-[#1A2622] rounded text-[#8CA099] hover:text-white" title="Pindah ke bawah">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>

                                    <!-- Teks Pilihan -->
                                    <div class="flex-1">
                                        <input type="text"
                                               name="options[{{ $idx }}][label]"
                                               class="ct-isian input-opsi-label text-sm !h-10"
                                               placeholder="Teks pilihan jawaban {{ $idx + 1 }}"
                                               value="{{ $opt['label'] ?? '' }}"
                                               required
                                               oninput="perbaruiPratinjau()">
                                    </div>

                                    <!-- Bobot Pilihan -->
                                    <div class="w-24">
                                        <input type="number"
                                               step="0.1"
                                               min="0"
                                               name="options[{{ $idx }}][weight]"
                                               class="ct-isian input-opsi-weight text-sm !h-10 text-right font-mono"
                                               placeholder="Bobot"
                                               value="{{ $opt['weight'] ?? 0 }}"
                                               required
                                               oninput="perbaruiPratinjau()">
                                    </div>

                                    <!-- Tombol Hapus -->
                                    <button type="button"
                                            onclick="hapusOpsi(this)"
                                            class="tombol-hapus-opsi p-2 rounded-lg text-rose-400 hover:bg-rose-500/10 hover:text-rose-300"
                                            aria-label="Hapus pilihan ke-{{ $idx + 1 }}"
                                            title="Hapus pilihan">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        @error('options')
                            <span class="ct-galat" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- 4. Status Aktif -->
                    <div class="p-3.5 rounded-xl border border-[#1E2B26] bg-[#111A17] flex items-center justify-between">
                        <div>
                            <span class="text-sm font-semibold text-white block">Status Pertanyaan</span>
                            <span class="text-xs text-[#8CA099]">Pertanyaan aktif akan langsung muncul di kuis pengguna baru.</span>
                        </div>
                        <label class="ct-saklar">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $soal->is_active))>
                            <span class="ct-saklar-gelang"></span>
                        </label>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex gap-3 pt-4 border-t border-[#1E2B26]">
                        <a href="{{ route('kuis.index') }}" class="ct-tombol ct-tombol--sekunder flex-1">
                            Batal
                        </a>
                        <button type="submit" class="ct-tombol ct-tombol--utama flex-1">
                            Simpan Pertanyaan
                        </button>
                    </div>
                </form>
            </x-kartu>
        </div>

        <!-- Kolom Kanan: Pratinjau Interaktif Kuis (5 Kolom) -->
        <div class="lg:col-span-5 space-y-4 sticky top-24">
            <x-kartu judul="Pratinjau Kuis Pengguna">
                <p class="text-xs text-[#8CA099] mb-4">
                    Tampilan pertanyaan seperti yang akan dilihat oleh pengguna baru pada kuis awal:
                </p>

                <div class="p-5 rounded-2xl border border-emerald-500/30 bg-[#0A0F0D] space-y-4">
                    <!-- Pertanyaan -->
                    <h4 id="pratinjau-teks-soal" class="text-base font-bold text-white leading-snug">
                        {{ $soal->question ?: 'Teks pertanyaan akan tampil di sini...' }}
                    </h4>

                    <!-- Pilihan Interaktif -->
                    <div id="pratinjau-opsi-list" class="space-y-2">
                        <!-- Diisi via JavaScript -->
                    </div>

                    <!-- Indikator Skor -->
                    <div class="pt-3 border-t border-[#1E2B26] flex items-center justify-between text-xs text-[#8CA099]">
                        <span>Bobot Pertanyaan: <strong id="pratinjau-bobot-soal" class="text-[#A3E635]">1.00</strong></span>
                        <span id="pratinjau-skor-pilihan" class="font-semibold text-white">Pilih salah satu jawaban</span>
                    </div>
                </div>
            </x-kartu>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function perbaruiPratinjau() {
        const pertanyaan = document.getElementById('input-pertanyaan').value;
        const charCount = document.getElementById('hitung-karakter');
        charCount.textContent = `${pertanyaan.length}/500`;

        document.getElementById('pratinjau-teks-soal').textContent = pertanyaan.trim() || 'Teks pertanyaan akan tampil di sini...';

        const bobot = parseFloat(document.getElementById('input-emission-weight').value) || 0;
        document.getElementById('pratinjau-bobot-soal').textContent = bobot.toFixed(2);

        // Perbarui pilihan di pratinjau
        const wadahPratinjau = document.getElementById('pratinjau-opsi-list');
        wadahPratinjau.innerHTML = '';

        const barisOpsi = document.querySelectorAll('.baris-opsi');
        barisOpsi.forEach((baris, idx) => {
            const label = baris.querySelector('.input-opsi-label').value.trim() || `Pilihan ${idx + 1}`;
            const weight = parseFloat(baris.querySelector('.input-opsi-weight').value) || 0;

            const div = document.createElement('div');
            div.className = 'p-3 rounded-xl border border-[#1E2B26] bg-[#111A17] hover:border-emerald-500/50 cursor-pointer flex items-center justify-between gap-2 transition-all';
            div.onclick = () => {
                document.querySelectorAll('#pratinjau-opsi-list > div').forEach(d => {
                    d.classList.remove('border-emerald-500', 'bg-emerald-950/20');
                });
                div.classList.add('border-emerald-500', 'bg-emerald-950/20');
                const totalNilai = (weight * bobot).toFixed(2);
                document.getElementById('pratinjau-skor-pilihan').innerHTML = `Nilai Garis Dasar: <span class="text-emerald-400 font-bold">${totalNilai}</span>`;
            };

            div.innerHTML = `
                <span class="text-sm text-[#E8EDEB]">${label}</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-[#1A2622] text-[#8CA099] font-mono">${weight}</span>
            `;
            wadahPratinjau.appendChild(div);
        });

        // Periksa batasan jumlah opsi (2 - 6)
        const total = barisOpsi.length;
        document.getElementById('tombol-tambah-opsi').style.display = total >= 6 ? 'none' : 'inline-flex';
        document.querySelectorAll('.tombol-hapus-opsi').forEach(btn => {
            btn.style.display = total <= 2 ? 'none' : 'inline-block';
        });
    }

    function tambahOpsi() {
        const wadah = document.getElementById('wadah-pilihan');
        const count = wadah.querySelectorAll('.baris-opsi').length;
        if (count >= 6) return;

        const newRow = document.createElement('div');
        newRow.className = 'baris-opsi flex items-center gap-2 p-3 rounded-xl border border-[#1E2B26] bg-[#111A17] transition-all';
        newRow.setAttribute('data-index', count);
        newRow.innerHTML = `
            <div class="flex flex-col gap-0.5">
                <button type="button" onclick="geserOpsi(this, -1)" class="p-1 hover:bg-[#1A2622] rounded text-[#8CA099] hover:text-white" title="Pindah ke atas">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button type="button" onclick="geserOpsi(this, 1)" class="p-1 hover:bg-[#1A2622] rounded text-[#8CA099] hover:text-white" title="Pindah ke bawah">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
            <div class="flex-1">
                <input type="text" name="options[${count}][label]" class="ct-isian input-opsi-label text-sm !h-10" placeholder="Teks pilihan jawaban ${count + 1}" required oninput="perbaruiPratinjau()">
            </div>
            <div class="w-24">
                <input type="number" step="0.1" min="0" name="options[${count}][weight]" class="ct-isian input-opsi-weight text-sm !h-10 text-right font-mono" placeholder="Bobot" value="1.0" required oninput="perbaruiPratinjau()">
            </div>
            <button type="button" onclick="hapusOpsi(this)" class="tombol-hapus-opsi p-2 rounded-lg text-rose-400 hover:bg-rose-500/10 hover:text-rose-300" aria-label="Hapus pilihan ke-${count + 1}" title="Hapus pilihan">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        `;

        wadah.appendChild(newRow);
        perbaruiIndeksOpsi();
        perbaruiPratinjau();
    }

    function hapusOpsi(btn) {
        const row = btn.closest('.baris-opsi');
        const count = document.querySelectorAll('.baris-opsi').length;
        if (count > 2) {
            row.remove();
            perbaruiIndeksOpsi();
            perbaruiPratinjau();
        }
    }

    function geserOpsi(btn, arah) {
        const row = btn.closest('.baris-opsi');
        if (arah === -1 && row.previousElementSibling) {
            row.parentNode.insertBefore(row, row.previousElementSibling);
        } else if (arah === 1 && row.nextElementSibling) {
            row.parentNode.insertBefore(row.nextElementSibling, row);
        }
        perbaruiIndeksOpsi();
        perbaruiPratinjau();
    }

    function perbaruiIndeksOpsi() {
        document.querySelectorAll('.baris-opsi').forEach((row, i) => {
            row.querySelector('.input-opsi-label').name = `options[${i}][label]`;
            row.querySelector('.input-opsi-weight').name = `options[${i}][weight]`;
            row.querySelector('.tombol-hapus-opsi').setAttribute('aria-label', `Hapus pilihan ke-${i + 1}`);
        });
    }

    document.addEventListener('DOMContentLoaded', perbaruiPratinjau);
</script>
@endpush
