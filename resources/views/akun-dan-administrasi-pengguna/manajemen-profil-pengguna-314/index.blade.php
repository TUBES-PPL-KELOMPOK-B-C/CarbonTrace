@extends('layouts.aplikasi-111')

@section('judul', 'Profil')

@push('aset')
    @vite(['resources/css/profil-314.css'])
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

            {{-- Kartu Ringkasan Rumah Tangga (PBI-02 Relasi) --}}
            <x-kartu judul="Konsumsi Rumah Tangga" subjudul="Dasar pembagi emisi listrik harian">
                @php
                    $setting = $user->userSetting;
                @endphp
                @if ($setting)
                    <div style="display: flex; flex-direction: column; gap: var(--ct-s2); font-size: 14px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--ct-text-muted);">Penghuni:</span>
                            <strong>{{ $setting->household_size }} orang</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--ct-text-muted);">Daya Listrik:</span>
                            <strong>{{ number_format($setting->power_watt, 0, ',', '.') }} Watt</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--ct-text-muted);">Kendaraan:</span>
                            <strong style="text-transform: capitalize;">{{ $setting->vehicle_ownership }}</strong>
                        </div>
                    </div>
                @else
                    <p style="font-size: 13px; color: var(--ct-text-muted); margin: 0 0 var(--ct-s3);">
                        Belum mengatur konsumsi rumah tangga. Emisi listrik saat ini dihitung perorangan.
                    </p>
                @endif
            </x-kartu>
        </aside>

        {{-- Kolom Kanan: Formulir Pembaruan Profil --}}
        <main>
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
        </main>
    </div>

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
