@extends('layouts.aplikasi-111')

@section('judul', 'Pengaturan Rumah Tangga')

@push('aset')
    @vite(['resources/css/rumah-tangga-314.css'])
@endpush

@section('isi')
    <x-notifikasi />

    <div class="rt-wadah">
        {{-- State Kosong: Ajakan Jika Belum Mengisi --}}
        @if (!$setting)
            <div class="rt-ajakan-kosong">
                <div>
                    <h3 style="margin: 0 0 4px; font-size: 16px; font-weight: 700; color: var(--ct-text);">
                        Isi data rumah tanggamu agar angka emisi listrik lebih tepat
                    </h3>
                    <p style="margin: 0; font-size: 14px; color: var(--ct-text-muted);">
                        Secara default emisi listrik dihitung perorangan. Menambahkan jumlah penghuni akan membagi emisi listrik rumah secara adil.
                    </p>
                </div>
            </div>
        @endif

        {{-- Kartu Utama Pengaturan Rumah Tangga --}}
        <x-kartu judul="Pengaturan Konsumsi Rumah Tangga" subjudul="Data dasar untuk pembagian kalkulasi emisi listrik dan mobilitas harianmu.">
            @include('akun-dan-administrasi-pengguna.pengaturan-konsumsi-rumah-tangga-314.form')
        </x-kartu>
    </div>

    {{-- Modal Konfirmasi Hapus Data Rumah Tangga --}}
    @if ($setting)
        <x-modal id="modal-hapus-rumah" judul="Hapus Data Rumah Tangga" :bahaya="true">
            <form method="POST" action="{{ route('rumah.destroy') }}">
                @csrf
                @method('DELETE')

                <p style="color: var(--ct-text-muted); font-size: 14px; margin-bottom: var(--ct-s4); line-height: 1.6;">
                    Apakah Anda yakin ingin menghapus data rumah tangga ini? Setelah dihapus, emisi listrik Anda akan dihitung penuh tanpa pembagian per penghuni. Riwayat pencatatan yang sudah lewat tidak akan terpengaruh.
                </p>

                <div style="display: flex; justify-content: flex-end; gap: var(--ct-s3); border-top: 1px solid var(--ct-border); padding-top: var(--ct-s4);">
                    <x-tombol tipe="button" varian="sekunder" onclick="tutupModal('modal-hapus-rumah')">
                        Batal
                    </x-tombol>
                    <x-tombol tipe="submit" varian="bahaya">
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
                    btn.innerHTML = '<span>Menyimpan...</span>';
                }
            });
        }
    </script>
@endsection
