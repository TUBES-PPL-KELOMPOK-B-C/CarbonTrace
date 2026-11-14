@extends('layouts.aplikasi-111')

@section('judul', 'Profil')

@push('aset')
    @vite(['resources/css/profil-314.css', 'resources/css/rumah-tangga-314.css'])
@endpush

@section('isi')
    <x-notifikasi />

    <div class="pr-tata-letak">
        {{-- Kolom Kiri: Kartu Identitas & Statistik Pengguna --}}
        <aside style="display: flex; flex-direction: column; gap: var(--ct-s5);">
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
                        <div class="pr-stat-nilai">{{ $user->streak_count ?? 0 }} <span style="font-size: 13px; font-weight: normal; color: var(--ct-text-muted);">hari</span></div>
                    </div>
                </div>

                <div style="width: 100%; padding: var(--ct-s3) 0; margin-bottom: var(--ct-s4); border-top: 1px solid var(--ct-border); border-bottom: 1px solid var(--ct-border); font-size: 13px; color: var(--ct-text-muted); display: flex; justify-content: space-between;">
                    <span>Bergabung sejak:</span>
                    <strong style="color: var(--ct-text);">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</strong>
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
        <main style="display: flex; flex-direction: column; gap: var(--ct-s5);">
            {{-- Kartu 1: Informasi Profil --}}
            <x-kartu judul="Informasi Profil" subjudul="Perbarui data pribadi dan identitas civitas akademikamu.">
                <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" class="pr-formulir" id="form-profil">
                    @csrf
                    @method('PUT')

                    {{-- Unggah Foto Profil dengan Pratinjau Langsung --}}
                    <div class="ct-isian-grup">
                        <label class="ct-label" for="photo">Foto Profil <span style="color: var(--ct-text-muted); font-size: 12px;">(Opsional, JPG/PNG maks 2 MB)</span></label>
                        <div class="pr-unggah-kotak">
                            <img id="pratinjau-foto"
                                 src="{{ $user->photo ? asset('storage/' . $user->photo) : 'data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 64 64%22 fill=%22%2310b981%22><circle cx=%2232%22 cy=%2232%22 r=%2232%22 fill=%22%23111a17%22/><text x=%2232%22 y=%2240%22 font-size=%2226%22 font-weight=%22bold%22 text-anchor=%22middle%22 fill=%22%2310b981%22>'.mb_strtoupper(mb_substr($user->name, 0, 1)).'</text></svg>' }}"
                                 alt="Pratinjau Foto"
                                 class="pr-unggah-pratinjau">
                            <div class="pr-unggah-info" style="flex: 1;">
                                <input type="file" id="photo" name="photo" accept="image/png,image/jpeg" class="ct-isian" style="padding-top: 10px; height: auto;" onchange="pratinjauBerkas(this)">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <span>Format: JPG, JPEG, PNG. Maks 2048 KB.</span>
                                    @if ($user->photo)
                                        <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; color: var(--ct-danger); font-size: 12px;">
                                            <input type="checkbox" name="hapus_foto" value="1">
                                            <span>Hapus Foto</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @error('photo')
                            <span class="ct-galat" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pr-formulir-baris-dua">
                        <x-isian label="Nama Lengkap" nama="name" :nilai="$user->name" :wajib="true" placeholder="Nama lengkap Anda" />
                        <x-isian label="Surel / Email" nama="email" tipe="email" :nilai="$user->email" :wajib="true" placeholder="nama@email.com" />
                    </div>

                    <div class="pr-formulir-baris-dua">
                        <x-isian label="Asal Kampus" nama="campus" :nilai="$user->campus" placeholder="Contoh: Telkom University" />
                        <x-isian label="Program Studi" nama="study_program" :nilai="$user->study_program" placeholder="Contoh: S1 Rekayasa Perangkat Lunak" />
                    </div>

                    <div class="pr-formulir-tombol">
                        <x-tombol tipe="button" varian="sekunder" onclick="bukaModal('modal-sandi')">
                            <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Ubah Kata Sandi
                        </x-tombol>

                        <x-tombol tipe="submit" varian="utama" id="tombol-simpan-profil">
                            Simpan Perubahan
                        </x-tombol>
                    </div>
                </form>
            </x-kartu>

            {{-- Kartu 2: Pengaturan Konsumsi Rumah Tangga (PBI-02) di Sisi Kanan --}}
            @php
                $setting = $user->userSetting;
            @endphp
            <x-kartu judul="Pengaturan Konsumsi Rumah Tangga" subjudul="Dasar pembagi emisi listrik PLN dan profil kepemilikan mobilitasmu.">
                @if ($setting)
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--ct-s4); margin-bottom: var(--ct-s4);">
                        <div style="background: var(--ct-surface-raised); border: 1px solid var(--ct-border); border-radius: var(--ct-r-ctrl); padding: var(--ct-s4);">
                            <div style="font-size: 13px; color: var(--ct-text-muted); margin-bottom: 4px;">Jumlah Penghuni:</div>
                            <div style="font-size: 20px; font-weight: 800; color: var(--ct-primary); display: flex; align-items: baseline; gap: 6px;">
                                {{ $setting->household_size }} orang
                                <span class="ct-pelat ct-pelat--aman" style="font-size: 11px; font-weight: 600;">
                                    Beban {{ number_format(100 / max(1, $setting->household_size), 1) }}%
                                </span>
                            </div>
                            <div style="font-size: 12px; color: var(--ct-text-muted); margin-top: 4px;">Emisi listrik rumah dibagi {{ $setting->household_size }}</div>
                        </div>

                        <div style="background: var(--ct-surface-raised); border: 1px solid var(--ct-border); border-radius: var(--ct-r-ctrl); padding: var(--ct-s4);">
                            <div style="font-size: 13px; color: var(--ct-text-muted); margin-bottom: 4px;">Daya Listrik Rumah:</div>
                            <div style="font-size: 20px; font-weight: 800; color: var(--ct-accent);">
                                {{ number_format($setting->power_watt, 0, ',', '.') }} <span style="font-size: 14px; font-weight: 600; color: var(--ct-text);">Watt</span>
                            </div>
                            <div style="font-size: 12px; color: var(--ct-text-muted); margin-top: 4px;">Kapasitas meteran PLN terpasang</div>
                        </div>

                        <div style="background: var(--ct-surface-raised); border: 1px solid var(--ct-border); border-radius: var(--ct-r-ctrl); padding: var(--ct-s4);">
                            <div style="font-size: 13px; color: var(--ct-text-muted); margin-bottom: 4px;">Aset Kendaraan:</div>
                            <div style="font-size: 18px; font-weight: 700; color: var(--ct-text); text-transform: capitalize; margin-top: 2px;">
                                {{ $setting->vehicle_ownership }}
                            </div>
                            <div style="font-size: 12px; color: var(--ct-text-muted); margin-top: 4px;">Profil kepemilikan keluarga</div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--ct-s3); padding-top: var(--ct-s3); border-top: 1px solid var(--ct-border);">
                        <span style="font-size: 13px; color: var(--ct-text-muted);">
                            * Perubahan berlaku untuk pencatatan emisi berikutnya.
                        </span>
                        <div style="display: flex; gap: var(--ct-s3);">
                            <x-tombol tipe="button" varian="utama" onclick="bukaModal('modal-rumah-tangga')">
                                Ubah Data Rumah Tangga (Pop-up)
                            </x-tombol>
                            <x-tombol href="{{ route('rumah.index') }}" varian="sekunder">
                                Halaman Penuh
                            </x-tombol>
                        </div>
                    </div>
                @else
                    <div class="rt-ajakan-kosong" style="margin-bottom: var(--ct-s4);">
                        <div>
                            <h4 style="margin: 0 0 4px; font-size: 15px; font-weight: 700; color: var(--ct-text);">
                                Isi data rumah tanggamu agar pembagian emisi listrik akurat
                            </h4>
                            <p style="margin: 0; font-size: 13px; color: var(--ct-text-muted);">
                                Rumah tangga dengan 4 penghuni membagi beban emisi listrik menjadi 25% per orang.
                            </p>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: var(--ct-s3);">
                        <x-tombol tipe="button" varian="utama" onclick="bukaModal('modal-rumah-tangga')">
                            Atur Data Rumah Tangga
                        </x-tombol>
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

            <p style="color: var(--ct-text-muted); font-size: 13px; margin-bottom: var(--ct-s4); line-height: 1.5;">
                Ubah data rumah tangga Anda di bawah ini lalu klik <strong>Simpan Pengaturan</strong> untuk langsung memperbarui data tanpa perlu menghapus data lama.
            </p>

            <div class="ct-isian-grup" style="margin-bottom: var(--ct-s4);">
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
                           oninput="document.getElementById('modal-angka-penghuni').textContent = this.value + ' orang'">
                    <span class="rt-penggeser-angka" id="modal-angka-penghuni" style="font-size: 15px; min-width: 80px;">
                        {{ old('household_size', $setting->household_size ?? 1) }} orang
                    </span>
                </div>
            </div>

            <div class="ct-isian-grup" style="margin-bottom: var(--ct-s4);">
                <label for="modal_power_watt" class="ct-label">Daya Listrik Rumah Terpasang (PLN)</label>
                <div class="ct-isian-satuan">
                    <input type="number"
                           id="modal_power_watt"
                           name="power_watt"
                           min="450"
                           max="50000"
                           step="50"
                           value="{{ old('power_watt', $setting->power_watt ?? 900) }}"
                           class="ct-isian"
                           required>
                    <span>watt</span>
                </div>
            </div>

            <div class="ct-isian-grup" style="margin-bottom: var(--ct-s5);">
                <span class="ct-label">Kepemilikan Kendaraan Rumah Tangga</span>
                <div class="rt-kartu-pilihan-kisi" style="grid-template-columns: repeat(2, 1fr);">
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
                        <label class="ct-pilihan-kartu" style="padding: var(--ct-s3);">
                            <input type="radio" name="vehicle_ownership" value="{{ $kunci }}" @checked($terpilihModal === $kunci) required>
                            <span class="ct-pilihan-kartu__label" style="font-size: 13px;">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--ct-s3); border-top: 1px solid var(--ct-border); padding-top: var(--ct-s4);">
                <x-tombol tipe="button" varian="sekunder" onclick="tutupModal('modal-rumah-tangga')">
                    Batal
                </x-tombol>
                <x-tombol tipe="submit" varian="utama">
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

            <x-isian label="Kata Sandi Saat Ini" nama="current_password" tipe="password" :wajib="true" placeholder="Masukkan kata sandi lama Anda" />
            <x-isian label="Kata Sandi Baru" nama="password" tipe="password" :wajib="true" placeholder="Minimal 8 karakter" />
            <x-isian label="Konfirmasi Kata Sandi Baru" nama="password_confirmation" tipe="password" :wajib="true" placeholder="Ulangi kata sandi baru" />

            <div class="flex justify-end gap-3 pt-3" style="display: flex; justify-content: flex-end; gap: var(--ct-s3); border-top: 1px solid var(--ct-border); margin-top: var(--ct-s4);">
                <x-tombol tipe="button" varian="sekunder" onclick="tutupModal('modal-sandi')">
                    Batal
                </x-tombol>
                <x-tombol tipe="submit" varian="utama">
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

            <p style="color: var(--ct-text-muted); font-size: 14px; margin-bottom: var(--ct-s4); line-height: 1.6;">
                Apakah Anda yakin ingin menonaktifkan akun? Setelah dinonaktifkan, Anda akan otomatis keluar dan tidak dapat masuk kembali ke sistem. Riwayat aktivitas dan jejak karbon Anda tetap tersimpan untuk integritas data.
            </p>

            <div class="ct-isian-grup" style="margin-bottom: var(--ct-s4);">
                <label class="flex items-center gap-2 cursor-pointer" style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" id="centang-setuju" required onchange="toggleTombolNonaktifkan(this)" style="margin-top: 4px;">
                    <span style="font-size: 14px; color: var(--ct-text);">Saya memahami konsekuensinya dan setuju menonaktifkan akun saya.</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-3" style="display: flex; justify-content: flex-end; gap: var(--ct-s3); border-top: 1px solid var(--ct-border);">
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
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggleTombolNonaktifkan(checkbox) {
            const btn = document.getElementById('tombol-eksekusi-nonaktifkan');
            if (btn) {
                btn.disabled = !checkbox.checked;
            }
        }

        // Efek tombol memuat saat formulir profil disimpan
        const formProfil = document.getElementById('form-profil');
        if (formProfil) {
            formProfil.addEventListener('submit', function() {
                const btn = document.getElementById('tombol-simpan-profil');
                if (btn) {
                    btn.disabled = true;
                    btn.setAttribute('aria-busy', 'true');
                    btn.innerHTML = '<span>Menyimpan...</span>';
                }
            });
        }
    </script>
@endsection
