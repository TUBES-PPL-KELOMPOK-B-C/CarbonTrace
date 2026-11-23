{{-- PBI-13: isian formulir lencana, dipakai modal tambah maupun modal ubah.
     $lencana null berarti modal tambah. $modalId dipakai supaya modal yang sama terbuka lagi
     kalau validasinya gagal.
     Dipanggil: @include('gamifikasi.manajemen-lencana-015.form', [
         'lencana' => null, 'jenis' => $jenis, 'modalId' => 'modal-lencana-tambah']) --}}
@php
    $lencana = $lencana ?? null;
    $jenis = $jenis ?? [];
    $modalId = $modalId ?? '';
    $awalan = $lencana ? 'ubah-'.$lencana->id.'-' : 'tambah-';
    $ikonPilihan = ['bintang', 'piala', 'mahkota', 'api', 'kalender', 'buku', 'kotak', 'jejak', 'bumi', 'roket'];
    // Nilai desimal ditampilkan tanpa angka nol di belakang koma: 1000 bukan 1000,0000.
    $nilaiSyarat = $lencana
        ? rtrim(rtrim(number_format((float) $lencana->required_value, 4, ',', ''), '0'), ',')
        : null;
@endphp

<input type="hidden" name="_modal" value="{{ $modalId }}">

<div class="space-y-4">
    <x-isian label="Nama lencana" nama="name" :id="$awalan.'name'" wajib
             :nilai="$lencana?->name" placeholder="Tujuh Hari Lurus" maxlength="120" />

    <x-isian label="Keterangan" nama="description" tipe="textarea" :id="$awalan.'description'"
             :nilai="$lencana?->description" rows="2"
             placeholder="Catat aktivitas tujuh hari berturut-turut." maxlength="500" />

    <div class="ct-isian-grup">
        <label for="{{ $awalan }}type" class="ct-label">Jenis syarat <span class="text-rose-500">*</span></label>
        <select name="type" id="{{ $awalan }}type" class="ct-isian" required
                aria-invalid="{{ $errors->has('type') ? 'true' : 'false' }}">
            @foreach ($jenis as $kunci => $satuan)
                <option value="{{ $kunci }}" @selected(old('type', $lencana?->type) === $kunci)>
                    {{ ucfirst($kunci) }} — diukur dalam {{ $satuan }}
                </option>
            @endforeach
        </select>
        @error('type')
            <span class="ct-galat" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <x-isian label="Angka syarat" nama="required_value" :id="$awalan.'required_value'" wajib
             :nilai="$nilaiSyarat" placeholder="1000" inputmode="decimal" />

    <div class="ct-isian-grup">
        <label for="{{ $awalan }}icon" class="ct-label">Ikon <span class="text-rose-500">*</span></label>
        <select name="icon" id="{{ $awalan }}icon" class="ct-isian" required
                aria-invalid="{{ $errors->has('icon') ? 'true' : 'false' }}">
            @foreach ($ikonPilihan as $ikon)
                <option value="{{ $ikon }}" @selected(old('icon', $lencana?->icon) === $ikon)>{{ ucfirst($ikon) }}</option>
            @endforeach
        </select>
        @error('icon')
            <span class="ct-galat" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <label class="flex items-center gap-2 text-sm" for="{{ $awalan }}is_active">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" id="{{ $awalan }}is_active" value="1"
               @checked(old('is_active', $lencana?->is_active ?? true))>
        <span>Aktifkan lencana ini — lencana aktif muncul di galeri semua pengguna.</span>
    </label>
</div>
