@extends('layouts.aplikasi-111')

@section('judul', 'Profil')

@push('aset')
    @vite(['resources/css/profil-314.css', 'resources/css/rumah-tangga-314.css'])
@endpush

@section('isi')
    <x-notifikasi />

    @php
        $setting = $user->setting;
        $adaGalatSandi = $errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation');
        $adaGalatNonaktifkan = $errors->has('nonaktifkan');
        $adaGalatRumah = $errors->has('household_size') || $errors->has('power_watt') || $errors->has('vehicle_ownership');
    @endphp

    <div class="pr-tata-letak">
        {{-- Kolom Kiri: Kartu Identitas & Statistik Pengguna --}}
        <aside class="pr-kolom-kiri">
            <x-kartu class="pr-kartu-identitas">
                <div class="pr-avatar-wadah">
                    @if ($user->photo)
                        <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="pr-avatar-foto" id="kartu-foto-avatar" />
                    @else
                        <div class="pr-avatar-inisial" id="kartu-inisial-avatar">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <h2 class="pr-identitas-nama">{{ $user->name }}</h2>
                <p class="pr-identitas-email">{{ $user->email }}</p>

                <div class="pr-pelat-grup">
                    <x-pelat varian="aman">
                        {{ $user->getRoleNames()->first() ?? ($user->role?->name ?? 'Pengguna') }}
                    </x-pelat>
                    @if ($user->is_active)
                        <x-pelat varian="aman">Akun Aktif</x-pelat>
                    @else
                        <x-pelat varian="terlampaui">Nonaktif</x-pelat>
                    @endif
                </div>

                <div class="pr-statistik-baris">
                    <div class="pr-stat-kotak">
                        <div class="pr-stat-label">EcoPoint</div>
                        <div class="pr-stat-nilai">{{ number_format($user->points ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="pr-stat-kotak">
                        <div class="pr-stat-label">Streak Berjalan</div>
                        <div class="pr-stat-nilai">{{ $user->streak_count ?? 0 }} <span class="pr-stat-satuan">hari</span></div>
                    </div>
                </div>

                <div class="pr-info-bergabung">
                    <span>Bergabung sejak:</span>
                    <strong>{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</strong>
                </div>

                <div class="pr-aksi-kiri">
                    <x-tombol tipe="button" varian="bahaya" onclick="bukaModal('modal-nonaktifkan')">
                        <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v10"/></svg>
                        Nonaktifkan Akun
                    </x-tombol>
                </div>
            </x-kartu>
        </aside>

        {{-- Kolom Kanan: Formulir Pembaruan Profil & Kartu Pengaturan Rumah Tangga --}}
        <main class="pr-kolom-kanan">
            {{-- Kartu 1: Informasi Profil --}}
            <x-kartu judul="Informasi Profil" subjudul="Perbarui data pribadi dan identitas civitas akademikamu.">
                @include('akun-dan-administrasi-pengguna.manajemen-profil-pengguna-314.form')
            </x-kartu>

            {{-- Kartu 2: Pengaturan Konsumsi Rumah Tangga (PBI-02) di Sisi Kanan --}}
            <x-kartu judul="Pengaturan Konsumsi Rumah Tangga" subjudul="Dasar pembagi emisi listrik PLN dan profil kepemilikan mobilitasmu.">
                @if ($setting)
                    <div class="pr-rt-kisi">
                        <div class="pr-rt-kotak">
                            <div class="pr-rt-label">Jumlah Penghuni:</div>
                            <div class="pr-rt-nilai">
                                {{ $setting->household_size }} orang
                                <span class="ct-pelat ct-pelat--aman pr-rt-pelat-kecil">
                                    Beban {{ number_format(100 / max(1, $setting->household_size), 1, ',', '.') }}%
                                </span>
                            </div>
                            <div class="pr-rt-keterangan">Emisi listrik rumah dibagi {{ $setting->household_size }}</div>
                        </div>

                        <div class="pr-rt-kotak">
                            <div class="pr-rt-label">Daya Listrik Rumah:</div>
                            <div class="pr-rt-nilai pr-rt-nilai--aksen">
                                {{ number_format($setting->power_watt, 0, ',', '.') }} <span class="pr-rt-satuan">Watt</span>
                            </div>
                            <div class="pr-rt-keterangan">Kapasitas meteran PLN terpasang</div>
                        </div>

                        <div class="pr-rt-kotak">
                            <div class="pr-rt-label">Aset Kendaraan:</div>
                            <div class="pr-rt-nilai--teks">
                                {{ $setting->vehicle_ownership }}
                            </div>
                            <div class="pr-rt-keterangan">Profil kepemilikan keluarga</div>
                        </div>
                    </div>

                    <div class="pr-rt-kaki">
                        <span class="pr-rt-catatan-kaki">
                            * Perubahan berlaku untuk pencatatan emisi berikutnya.
                        </span>
                        <div class="pr-rt-aksi">
                            <x-tombol tipe="button" varian="utama" onclick="bukaModal('modal-rumah-tangga')">
                                Ubah Data Rumah Tangga
                            </x-tombol>
                            <x-tombol href="{{ route('rumah.index') }}" varian="sekunder">
                                Halaman Penuh
                            </x-tombol>
                        </div>
                    </div>
                @else
                    <div class="rt-ajakan-kosong rt-ajakan-kosong--profil">
                        <div>
                            <h4 class="rt-ajakan-kosong__judul">
                                Isi data rumah tanggamu agar pembagian emisi listrik akurat
                            </h4>
                            <p class="rt-ajakan-kosong__teks">
                                Rumah tangga dengan 4 penghuni membagi beban emisi listrik menjadi 25% per orang.
                            </p>
                        </div>
                        <div class="rt-ajakan-kosong__aksi">
                            <x-tombol tipe="button" varian="utama" onclick="bukaModal('modal-rumah-tangga')">
                                Atur Data Rumah Tangga
                            </x-tombol>
                        </div>
                    </div>
                @endif
            </x-kartu>
        </main>
    </div>

    {{-- Modal Pop-Up Ubah Data Rumah Tangga (PBI-02) --}}
    <x-modal id="modal-rumah-tangga" judul="Pengaturan Konsumsi Rumah Tangga">
        <form method="POST" action="{{ route('rumah.upsert') }}" id="form-modal-rumah">
            @csrf
            @method('PUT')

            <p class="pr-modal-deskripsi">
                Ubah data rumah tanggamu di bawah ini lalu klik <strong>Simpan Pengaturan</strong> untuk langsung memperbarui data tanpa perlu menghapus data lama.
            </p>

            <div class="ct-isian-grup pr-modal-grup">
                <label for="modal_household_size" class="ct-label">Jumlah Penghuni Rumah (1 - 20 orang)</label>
                <div class="rt-penggeser-baris">
                    <input type="range"
                           id="modal_household_size"
                           name="household_size"
                           min="1"
                           max="20"
                           step="1"
                           value="{{ old('household_size', $setting->household_size ?? 1) }}"
                           class="ct-penggeser"
                           aria-describedby="@error('household_size') galat-modal-penghuni @enderror"
                           @error('household_size') aria-invalid="true" @enderror
                           oninput="document.getElementById('modal-angka-penghuni').textContent = this.value + ' orang'">
                    <span class="rt-penggeser-angka rt-penggeser-angka--modal" id="modal-angka-penghuni">
                        {{ old('household_size', $setting->household_size ?? 1) }} orang
                    </span>
                </div>
                @error('household_size')
                    <span class="ct-galat" id="galat-modal-penghuni" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="pr-modal-grup">
                <x-isian
                    label="Daya Listrik Rumah Terpasang (PLN)"
                    nama="power_watt"
                    id="modal_power_watt"
                    tipe="number"
                    satuan="watt"
                    :nilai="old('power_watt', $setting->power_watt ?? 900)"
                    :wajib="true"
                    min="450"
                    max="50000"
                    step="50"
                    placeholder="Contoh: 900, 1300, 2200" />
            </div>

            <div class="ct-isian-grup pr-modal-grup--akhir">
                <span class="ct-label">Kepemilikan Kendaraan Rumah Tangga</span>
                <div class="rt-kartu-pilihan-kisi rt-kartu-pilihan-kisi--dua">
                    @php
                        $opsiModal = [
                            'tidak ada' => 'Tidak Ada',
                            'motor' => 'Sepeda Motor',
                            'mobil' => 'Mobil',
                            'keduanya' => 'Keduanya',
                        ];
                        $terpilihModal = old('vehicle_ownership', $setting->vehicle_ownership ?? 'tidak ada');
                    @endphp
                    @foreach ($opsiModal as $kunci => $label)
                        <label class="ct-pilihan-kartu ct-pilihan-kartu--padat">
                            <input type="radio" name="vehicle_ownership" value="{{ $kunci }}" @checked($terpilihModal === $kunci) required>
                            <span class="ct-pilihan-kartu__label ct-pilihan-kartu__label--kecil">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('vehicle_ownership')
                    <span class="ct-galat" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="pr-modal-kaki">
                <x-tombol tipe="button" varian="sekunder" onclick="tutupModal('modal-rumah-tangga')">
                    Batal
                </x-tombol>
                <x-tombol tipe="submit" varian="utama" id="tombol-simpan-modal-rumah">
                    Simpan Pengaturan
                </x-tombol>
            </div>
        </form>
    </x-modal>

    {{-- Modal Ubah Kata Sandi --}}
    <x-modal id="modal-sandi" judul="Ubah Kata Sandi">
        <form method="POST" action="{{ route('profil.sandi') }}" id="form-sandi" class="pr-formulir">
            @csrf
            @method('PUT')

            <x-isian label="Kata Sandi Saat Ini" nama="current_password" tipe="password" :wajib="true" placeholder="Masukkan kata sandi lama kamu" />
            <x-isian label="Kata Sandi Baru" nama="password" tipe="password" :wajib="true" placeholder="Minimal 8 karakter" />
            <x-isian label="Konfirmasi Kata Sandi Baru" nama="password_confirmation" tipe="password" :wajib="true" placeholder="Ulangi kata sandi baru" />

            <div class="pr-modal-kaki">
                <x-tombol tipe="button" varian="sekunder" onclick="tutupModal('modal-sandi')">
                    Batal
                </x-tombol>
                <x-tombol tipe="submit" varian="utama" id="tombol-simpan-sandi">
                    Simpan Sandi Baru
                </x-tombol>
            </div>
        </form>
    </x-modal>

    {{-- Modal Konfirmasi Nonaktifkan Akun --}}
    <x-modal id="modal-nonaktifkan" judul="Nonaktifkan Akun" :bahaya="true">
        <form method="POST" action="{{ route('profil.destroy') }}" id="form-nonaktifkan" class="pr-formulir">
            @csrf
            @method('DELETE')

            @error('nonaktifkan')
                <div class="pr-modal-galat" role="alert">{{ $message }}</div>
            @enderror

            <p class="pr-modal-deskripsi">
                Apakah kamu yakin ingin menonaktifkan akun? Setelah dinonaktifkan, kamu akan otomatis keluar dari seluruh perangkat dan tidak dapat masuk kembali ke sistem. Riwayat aktivitas dan jejak karbonmu tetap tersimpan untuk integritas data.
            </p>

            <div class="ct-isian-grup pr-modal-grup">
                <label class="pr-modal-centang-label">
                    <input type="checkbox" id="centang-setuju" class="pr-modal-centang-input" required onchange="toggleTombolNonaktifkan(this)">
                    <span class="pr-modal-centang-teks">Saya memahami konsekuensinya dan setuju menonaktifkan akun saya.</span>
                </label>
            </div>

            <div class="pr-modal-kaki">
                <x-tombol tipe="button" varian="sekunder" onclick="tutupModal('modal-nonaktifkan')">
                    Batal
                </x-tombol>
                <x-tombol tipe="submit" varian="bahaya" id="tombol-eksekusi-nonaktifkan" disabled>
                    Ya, Nonaktifkan Akun Saya
                </x-tombol>
            </div>
        </form>
    </x-modal>

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

        // Tutup modal jika menekan tombol Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupModal('modal-sandi');
                tutupModal('modal-nonaktifkan');
                tutupModal('modal-rumah-tangga');
            }
        });

        function pratinjauBerkas(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const pratinjau = document.getElementById('pratinjau-foto');
                    if (pratinjau) {
                        pratinjau.src = e.target.result;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggleTombolNonaktifkan(checkbox) {
            const btn = document.getElementById('tombol-eksekusi-nonaktifkan');
            if (btn) {
                btn.disabled = !checkbox.checked;
            }
        }

        function pasangKeadaanMemuat(formId, tombolId, teksMemuat) {
            const form = document.getElementById(formId);
            if (!form) return;
            form.addEventListener('submit', function() {
                const btn = document.getElementById(tombolId);
                if (btn) {
                    btn.disabled = true;
                    btn.setAttribute('aria-busy', 'true');
                    btn.innerHTML = '<span class="ct-spinner" aria-hidden="true"></span><span>' + teksMemuat + '</span>';
                }
            });
        }

        pasangKeadaanMemuat('form-profil', 'tombol-simpan-profil', 'Menyimpan...');
        pasangKeadaanMemuat('form-sandi', 'tombol-simpan-sandi', 'Menyimpan...');
        pasangKeadaanMemuat('form-nonaktifkan', 'tombol-eksekusi-nonaktifkan', 'Memproses...');
        pasangKeadaanMemuat('form-modal-rumah', 'tombol-simpan-modal-rumah', 'Menyimpan...');

        // Buka modal otomatis apabila terdapat galat validasi setelah redirect
        document.addEventListener('DOMContentLoaded', function() {
            @if ($adaGalatSandi)
                bukaModal('modal-sandi');
            @elseif ($adaGalatNonaktifkan)
                bukaModal('modal-nonaktifkan');
            @elseif ($adaGalatRumah)
                bukaModal('modal-rumah-tangga');
            @endif
        });
    </script>
@endsection
