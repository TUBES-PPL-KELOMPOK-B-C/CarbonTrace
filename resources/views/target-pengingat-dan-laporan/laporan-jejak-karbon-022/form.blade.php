{{-- Formulir laporan, dipakai untuk menyusun (susun) dan mengubah (show). $laporan null berarti laporan baru. --}}
@php
    $lingkup = $laporan?->kategoriIds() ?? [];
    $terpilih = old('title') !== null || $errors->any()
        ? array_map('intval', (array) old('kategori', []))
        : ($lingkup ?: $kategori->pluck('id')->all());
    $bawaan = $laporan ? [$laporan->period_start, $laporan->period_end] : $pintasan['30 hari'];
@endphp

<form method="POST" action="{{ $laporan ? route('laporan.update', $laporan) : route('laporan.store') }}" class="tg-form" novalidate
      data-form-kunci data-form-laporan data-api="{{ route('laporan.pratinjau') }}">
    @csrf
    @if ($laporan)
        @method('PUT')
    @endif

    <div class="ct-isian-grup">
        <label class="ct-label" for="laporan-judul">Judul laporan</label>
        <input class="ct-isian" type="text" id="laporan-judul" name="title" maxlength="120" required
               value="{{ old('title', $laporan?->title) }}" placeholder="Contoh: Laporan Oktober 2026"
               @error('title') aria-invalid="true" aria-describedby="galat-judul" @enderror>
        @error('title')<p class="ct-galat" id="galat-judul">{{ $message }}</p>@enderror
    </div>

    <fieldset class="tg-form__periode">
        <legend class="ct-label">Rentang tanggal</legend>
        <div class="tg-pintasan">
            @foreach ($pintasan as $label => [$mulai, $selesai])
                <button type="button" class="ct-tombol ct-tombol--sekunder" data-mulai="{{ $mulai }}" data-selesai="{{ $selesai }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="tg-form__tanggal">
            <div class="ct-isian-grup">
                <label class="ct-label" for="laporan-mulai">Tanggal mulai</label>
                <input class="ct-isian" type="date" id="laporan-mulai" name="period_start" max="{{ $hariIni }}" required data-isian-mulai
                       value="{{ old('period_start', $bawaan[0]) }}" aria-describedby="galat-mulai" @error('period_start') aria-invalid="true" @enderror>
                <p class="ct-galat" id="galat-mulai" data-galat="period_start">{{ $errors->first('period_start') }}</p>
            </div>
            <div class="ct-isian-grup">
                <label class="ct-label" for="laporan-selesai">Tanggal selesai</label>
                <input class="ct-isian" type="date" id="laporan-selesai" name="period_end" max="{{ $hariIni }}" required data-isian-selesai
                       value="{{ old('period_end', $bawaan[1]) }}" aria-describedby="galat-selesai" @error('period_end') aria-invalid="true" @enderror>
                <p class="ct-galat" id="galat-selesai" data-galat="period_end">{{ $errors->first('period_end') }}</p>
            </div>
        </div>
    </fieldset>

    <fieldset class="tg-form__periode">
        <legend class="ct-label">Kategori</legend>
        @foreach ($kategori as $k)
            <label class="lj-centang">
                <input type="checkbox" name="kategori[]" value="{{ $k->id }}" @checked(in_array($k->id, $terpilih))>
                <span>{{ $k->name }}</span>
            </label>
        @endforeach
        <p class="lj-catatan">Tanpa centang sama sekali berarti semua kategori.</p>
        @error('kategori.*')<p class="ct-galat">{{ $message }}</p>@enderror
    </fieldset>

    <button type="submit" class="ct-tombol ct-tombol--utama tg-form__kirim" data-teks-memuat="Menyimpan...">{{ $laporan ? 'Simpan Perubahan' : 'Simpan Laporan' }}</button>
</form>
