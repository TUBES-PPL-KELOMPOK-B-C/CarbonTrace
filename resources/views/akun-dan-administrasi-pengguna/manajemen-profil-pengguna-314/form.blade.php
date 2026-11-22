<form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" class="pr-formulir" id="form-profil">
    @csrf
    @method('PUT')

    {{-- Unggah Foto Profil dengan Pratinjau Langsung --}}
    <div class="ct-isian-grup">
        <label class="ct-label" for="photo">
            Foto Profil <span class="pr-label-opsional">(Opsional, JPG/PNG maks 2 MB)</span>
        </label>
        <div class="pr-unggah-kotak">
            <img id="pratinjau-foto"
                 src="{{ $user->photo ? asset('storage/' . $user->photo) : 'data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 64 64%22 fill=%22%2310b981%22><circle cx=%2232%22 cy=%2232%22 r=%2232%22 fill=%22%23111a17%22/><text x=%2232%22 y=%2240%22 font-size=%2226%22 font-weight=%22bold%22 text-anchor=%22middle%22 fill=%22%2310b981%22>'.mb_strtoupper(mb_substr($user->name, 0, 1)).'</text></svg>' }}"
                 alt="Pratinjau Foto"
                 class="pr-unggah-pratinjau">
            <div class="pr-unggah-info">
                <input type="file"
                       id="photo"
                       name="photo"
                       accept="image/png,image/jpeg"
                       class="ct-isian pr-unggah-berkas"
                       aria-describedby="ket-foto @error('photo') galat-foto @enderror"
                       @error('photo') aria-invalid="true" @enderror
                       onchange="pratinjauBerkas(this)">
                <div class="pr-unggah-bawah">
                    <span id="ket-foto">Format: JPG, JPEG, PNG. Maks 2048 KB.</span>
                    @if ($user->photo)
                        <label class="pr-hapus-foto">
                            <input type="checkbox" name="hapus_foto" value="1">
                            <span>Hapus Foto</span>
                        </label>
                    @endif
                </div>
            </div>
        </div>
        @error('photo')
            <span class="ct-galat" id="galat-foto" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <div class="pr-formulir-baris-dua">
        <x-isian label="Nama Lengkap" nama="name" :nilai="$user->name" :wajib="true" placeholder="Nama lengkap kamu" />
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
