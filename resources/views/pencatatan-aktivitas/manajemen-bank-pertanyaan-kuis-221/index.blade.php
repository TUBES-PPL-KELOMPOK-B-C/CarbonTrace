@extends('layouts.app-221')

@section('title', 'Manajemen Bank Pertanyaan Kuis')
@section('page_title', 'Bank Pertanyaan Kuis (Admin)')

@section('content')
<div class="space-y-6">

    <!-- Header & Indikator Kuis Aktif -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-white">Bank Pertanyaan Kuis Onboarding</h2>
                <x-pelat :varian="$totalAktif >= 6 ? 'aman' : 'hampir'">
                    {{ $totalAktif }} Aktif dari {{ $totalSemua }} Soal
                </x-pelat>
            </div>
            <p class="text-sm text-[#8CA099] mt-1">
                Kuis awal membutuhkan minimal 6 pertanyaan aktif untuk menaksir garis dasar emisi pengguna baru.
            </p>
        </div>

        <a href="{{ route('kuis.create') }}" class="ct-tombol ct-tombol--utama text-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pertanyaan
        </a>
    </div>

    <!-- Peringatan jika < 6 soal aktif -->
    @if($totalAktif < 6)
        <div class="p-4 rounded-xl border border-amber-500/30 bg-amber-500/10 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="text-sm">
                <span class="font-bold text-amber-300">Perhatian:</span>
                <span class="text-amber-200/90"> Saat ini baru ada {{ $totalAktif }} pertanyaan aktif. Tambahkan minimal {{ 6 - $totalAktif }} pertanyaan lagi agar kuis awal garis dasar dapat berjalan bagi pengguna baru.</span>
            </div>
        </div>
    @endif

    <!-- Tabel Soal Kuis -->
    <x-kartu>
        @if($pertanyaan->isEmpty())
            <x-kosong
                pesan="Bank pertanyaan masih kosong. Tambahkan minimal enam pertanyaan agar kuis awal bisa dijalankan."
                subpesan="Pertanyaan akan digunakan untuk menghitung emisi dasar pengguna baru pada saat pendaftaran."
            >
                <x-slot:aksi>
                    <a href="{{ route('kuis.create') }}" class="ct-tombol ct-tombol--utama">
                        Tambah Pertanyaan Pertama
                    </a>
                </x-slot:aksi>
            </x-kosong>
        @else
            <x-tabel :kepala="['Pertanyaan', 'Pilihan Jawaban (Bobot)', 'Bobot Emisi', 'Responden', 'Status', 'Aksi']">
                @foreach($pertanyaan as $soal)
                    @php
                        $jumlahJawaban = $soal->answerCount();
                    @endphp
                    <tr class="hover:bg-[#1A2622]/40 transition-colors">
                        <td data-label="Pertanyaan" class="text-sm font-semibold text-white max-w-[280px]">
                            {{ $soal->question }}
                        </td>

                        <td data-label="Pilihan Jawaban">
                            <div class="space-y-1 py-1">
                                @foreach($soal->options ?? [] as $opt)
                                    <div class="text-xs text-[#8CA099] flex items-center justify-between gap-2 max-w-[240px]">
                                        <span class="truncate">• {{ $opt['label'] }}</span>
                                        <span class="font-bold text-[#E8EDEB] shrink-0 font-mono">({{ $opt['weight'] }})</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>

                        <td data-label="Bobot Emisi" class="angka text-sm font-bold text-[#A3E635]">
                            {{ str_replace('.', ',', (string) round((float) $soal->emission_weight, 2)) }}
                        </td>

                        <td data-label="Responden" class="text-xs text-[#8CA099]">
                            @if($jumlahJawaban > 0)
                                <span class="font-semibold text-white">{{ $jumlahJawaban }}</span> pengguna
                            @else
                                <span class="text-neutral-500">Belum ada</span>
                            @endif
                        </td>

                        <!-- Saklar Status Aktif / Nonaktif -->
                        <td data-label="Status">
                            <form method="POST" action="{{ route('kuis.status', $soal) }}">
                                @csrf
                                @method('PATCH')
                                <label class="ct-saklar" title="{{ $soal->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                    <input type="checkbox" onchange="this.form.submit()" @checked($soal->is_active)>
                                    <span class="ct-saklar-gelang"></span>
                                </label>
                            </form>
                        </td>

                        <td data-label="Aksi" class="text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('kuis.edit', $soal) }}" class="p-2 rounded-lg hover:bg-[#1A2622] text-[#8CA099] hover:text-white" title="Ubah pertanyaan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                @if($jumlahJawaban === 0)
                                    <button type="button" onclick="konfirmasiHapusSoal('{{ $soal->id }}', '{{ addslashes($soal->question) }}')" class="p-2 rounded-lg hover:bg-[#1A2622] text-rose-400 hover:text-rose-300" title="Hapus pertanyaan">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @else
                                    <span class="p-2 text-neutral-600 cursor-not-allowed" title="Pertanyaan yang sudah dijawab tidak dapat dihapus permanen">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-3.5a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-tabel>

            <div class="mt-4">
                {{ $pertanyaan->links() }}
            </div>
        @endif
    </x-kartu>

</div>

<!-- Dialog Konfirmasi Hapus Modal -->
<x-modal id="modal-hapus-soal" judul="Hapus Pertanyaan Kuis" :bahaya="true">
    <p class="text-sm text-[#8CA099] mb-4">
        Apakah kamu yakin ingin menghapus pertanyaan berikut secara permanen?
        <span id="teks-hapus-soal" class="block font-bold text-white mt-1"></span>
    </p>
    <form id="form-hapus-soal" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('modal-hapus-soal').classList.add('hidden')" class="ct-tombol ct-tombol--sekunder !h-10 text-sm">
                Batal
            </button>
            <button type="submit" class="ct-tombol ct-tombol--bahaya !h-10 text-sm">
                Hapus Permanen
            </button>
        </div>
    </form>
</x-modal>

@endsection

@push('scripts')
<script>
    function konfirmasiHapusSoal(id, question) {
        document.getElementById('teks-hapus-soal').textContent = `"${question}"`;
        document.getElementById('form-hapus-soal').action = `/admin/bank-kuis/${id}`;
        document.getElementById('modal-hapus-soal').classList.remove('hidden');
    }
</script>
@endpush
