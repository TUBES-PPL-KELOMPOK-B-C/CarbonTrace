@extends('layouts.aplikasi-111')

@section('judul', 'Pengaturan Rumah Tangga')

@push('aset')
    @vite(['resources/css/rumah-tangga-314.css'])
@endpush

@section('isi')
    <x-notifikasi />

    <div class="rt-wadah">
        @error('hapus_rumah')
            <div class="rt-galat-kotak" role="alert">{{ $message }}</div>
        @enderror

        {{-- State Kosong: Ajakan Jika Belum Mengisi (dengan tombol aksi) --}}
        @if (!$setting)
            <div class="rt-ajakan-kosong">
                <div>
                    <h3 class="rt-ajakan-kosong__judul">
                        Isi data rumah tanggamu agar angka emisi listrik lebih tepat
                    </h3>
                    <p class="rt-ajakan-kosong__teks">
                        Secara bawaan emisi listrik dihitung perorangan. Menambahkan jumlah penghuni akan membagi emisi listrik rumah secara adil.
                    </p>
                </div>
                <div class="rt-ajakan-kosong__aksi">
                    <x-tombol tipe="button" varian="utama" onclick="fokusFormulirRumah()">
                        Isi Data Sekarang
                    </x-tombol>
                </div>
            </div>
        @endif

        {{-- Kartu Utama Pengaturan Rumah Tangga --}}
        <x-kartu judul="Pengaturan Konsumsi Rumah Tangga" subjudul="Data dasar untuk pembagian kalkulasi emisi listrik dan mobilitas harianmu.">
            {{-- State Memuat: Kerangka Abu-abu (Skeleton) --}}
            <div id="rt-kerangka-memuat" class="rt-kerangka-wadah hidden" aria-hidden="true">
                <x-kerangka jenis="teks" :jumlah="2" />
                <x-kerangka jenis="kartu" :jumlah="4" />
                <x-kerangka jenis="teks" :jumlah="3" />
            </div>

            <div id="rt-konten-formulir">
                @include('akun-dan-administrasi-pengguna.pengaturan-konsumsi-rumah-tangga-314.form')
            </div>
        </x-kartu>
    </div>

    {{-- Modal Konfirmasi Hapus Data Rumah Tangga --}}
    @if ($setting)
        <x-modal id="modal-hapus-rumah" judul="Hapus Data Rumah Tangga" :bahaya="true">
            <form method="POST" action="{{ route('rumah.destroy') }}" id="form-hapus-rumah">
                @csrf
                @method('DELETE')

                <p class="rt-modal-deskripsi">
                    Apakah kamu yakin ingin menghapus data rumah tangga ini? Setelah dihapus, emisi listrikmu akan dihitung penuh tanpa pembagian per penghuni. Riwayat pencatatan yang sudah lewat tidak akan terpengaruh.
                </p>

                <div class="rt-modal-kaki">
                    <x-tombol tipe="button" varian="sekunder" onclick="tutupModal('modal-hapus-rumah')">
                        Batal
                    </x-tombol>
                    <x-tombol tipe="submit" varian="bahaya" id="tombol-eksekusi-hapus-rumah">
                        Ya, Hapus Data
                    </x-tombol>
                </div>
            </form>
        </x-modal>
    @endif

    <script>
        function bukaModal(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.remove('hidden');
                m.removeAttribute('hidden');
                m.style.display = 'flex';
            }
        }

        function tutupModal(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.add('hidden');
                m.setAttribute('hidden', '');
                m.style.display = 'none';
            }
        }

        function fokusFormulirRumah() {
            const target = document.getElementById('household_size');
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.focus();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupModal('modal-hapus-rumah');
            }
        });

        const formRt = document.getElementById('form-rumah-tangga');
        if (formRt) {
            formRt.addEventListener('submit', function() {
                const btn = document.getElementById('tombol-simpan-rumah');
                if (btn) {
                    btn.disabled = true;
                    btn.setAttribute('aria-busy', 'true');
                    btn.innerHTML = '<span class="ct-spinner" aria-hidden="true"></span><span>Menyimpan...</span>';
                }
                const kerangka = document.getElementById('rt-kerangka-memuat');
                const konten = document.getElementById('rt-konten-formulir');
                if (kerangka && konten) {
                    kerangka.classList.remove('hidden');
                    kerangka.removeAttribute('aria-hidden');
                }
            });
        }

        const formHapusRt = document.getElementById('form-hapus-rumah');
        if (formHapusRt) {
            formHapusRt.addEventListener('submit', function() {
                const btn = document.getElementById('tombol-eksekusi-hapus-rumah');
                if (btn) {
                    btn.disabled = true;
                    btn.setAttribute('aria-busy', 'true');
                    btn.innerHTML = '<span class="ct-spinner" aria-hidden="true"></span><span>Menghapus...</span>';
                }
            });
        }
    </script>
@endsection
