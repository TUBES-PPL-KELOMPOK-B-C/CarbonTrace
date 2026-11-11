{{-- Formulir tetapkan/ubah target di dalam panel geser. Beberapa formulir bisa ada di satu halaman,
     jadi nilai lama dan galat hanya dipakai oleh panel yang tadi dikirim (penanda _panel). --}}
@use('App\Services\RingkasanTarget022')
@php
    $hariIni = RingkasanTarget022::hariIni();
    $bulanIni = [$hariIni->toDateString(), $hariIni->copy()->endOfMonth()->toDateString()];
    $bulanDepan = [$hariIni->copy()->addMonthNoOverflow()->startOfMonth()->toDateString(), $hariIni->copy()->addMonthNoOverflow()->endOfMonth()->toDateString()];
    $bawaan = $target ? [$target->period_start, $target->period_end] : ($adaBerjalan ? $bulanDepan : $bulanIni);

    $panelIni = old('_panel') === $panelId;
    $nilai = fn (string $kolom, $asal) => $panelIni ? old($kolom) : $asal;
    $galat = fn (string $kolom) => $panelIni ? $errors->first($kolom) : null;
    $batasAwal = $target ? rtrim(rtrim(number_format((float) $target->emission_limit, 2, ',', ''), '0'), ',') : '';
@endphp

<form method="POST" action="{{ $target ? route('target.update', $target) : route('target.store') }}" class="tg-form" novalidate data-form-kunci>
    @csrf
    @if ($target)
        @method('PUT')
    @endif
    <input type="hidden" name="_panel" value="{{ $panelId }}">

    <fieldset class="tg-form__periode">
        <legend class="ct-label">Periode</legend>
        <div class="tg-pintasan">
            {{-- Bulan ini dimulai hari ini, supaya sisa kuota dihitung dari tanggal mulai target --}}
            @if ($bulanIni[0] < $bulanIni[1])
                <button type="button" class="ct-tombol ct-tombol--sekunder" data-mulai="{{ $bulanIni[0] }}" data-selesai="{{ $bulanIni[1] }}">Bulan ini</button>
            @endif
            <button type="button" class="ct-tombol ct-tombol--sekunder" data-mulai="{{ $bulanDepan[0] }}" data-selesai="{{ $bulanDepan[1] }}">Bulan depan</button>
        </div>

        <div class="tg-form__tanggal">
            <div class="ct-isian-grup">
                <label class="ct-label" for="{{ $panelId }}-mulai">Tanggal mulai</label>
                <input class="ct-isian" type="date" id="{{ $panelId }}-mulai" name="period_start" value="{{ $nilai('period_start', $bawaan[0]) }}" required
                       data-isian-mulai @if ($galat('period_start')) aria-invalid="true" aria-describedby="{{ $panelId }}-galat-mulai" @endif>
                @if ($galat('period_start'))<p class="ct-galat" id="{{ $panelId }}-galat-mulai">{{ $galat('period_start') }}</p>@endif
            </div>
            <div class="ct-isian-grup">
                <label class="ct-label" for="{{ $panelId }}-selesai">Tanggal selesai</label>
                <input class="ct-isian" type="date" id="{{ $panelId }}-selesai" name="period_end" value="{{ $nilai('period_end', $bawaan[1]) }}" required
                       data-isian-selesai @if ($galat('period_end')) aria-invalid="true" aria-describedby="{{ $panelId }}-galat-selesai" @endif>
                @if ($galat('period_end'))<p class="ct-galat" id="{{ $panelId }}-galat-selesai">{{ $galat('period_end') }}</p>@endif
            </div>
        </div>
    </fieldset>

    <div class="ct-isian-grup">
        <label class="ct-label" for="{{ $panelId }}-batas">Batas emisi</label>
        <div class="tg-satuan">
            <input class="ct-isian" type="text" inputmode="decimal" id="{{ $panelId }}-batas" name="emission_limit" required
                   value="{{ $nilai('emission_limit', $batasAwal) }}" placeholder="{{ $saran ? \App\Services\PerhitunganEmisi111::angka($saran, 1) : 'Contoh: 150' }}"
                   @if ($galat('emission_limit')) aria-invalid="true" aria-describedby="{{ $panelId }}-galat-batas" @endif>
            <span>kg CO2e</span>
        </div>
        @if ($galat('emission_limit'))<p class="ct-galat" id="{{ $panelId }}-galat-batas">{{ $galat('emission_limit') }}</p>@endif
    </div>

    <button type="submit" class="ct-tombol ct-tombol--utama tg-form__kirim" data-teks-memuat="Menyimpan...">{{ $target ? 'Simpan Perubahan' : 'Tetapkan Target' }}</button>
</form>
