{{-- Formulir tambah/ubah pengingat di dalam panel geser. Roda putar jam untuk tetikus dan sentuh;
     kolom ketik di bawahnya adalah isian sebenarnya dan alternatif bagi pengguna papan ketik. --}}
@php
    $panelIni = old('_panel') === $panelId;
    $jamAwal = $panelIni ? old('remind_time') : ($pengingat?->jam() ?? '20:00');
    $galat = $panelIni ? $errors->first('remind_time') : null;
@endphp

<form method="POST" action="{{ $pengingat ? route('pengingat.update', $pengingat) : route('pengingat.store') }}" class="pg-form" novalidate data-form-kunci>
    @csrf
    @if ($pengingat)
        @method('PUT')
    @endif
    <input type="hidden" name="_panel" value="{{ $panelId }}">

    <div class="ct-roda-jam" data-roda-jam aria-hidden="true">
        <div class="ct-roda-jam__kolom" data-roda="jam">
            @foreach (range(0, 23) as $j)
                <button type="button" class="ct-roda-jam__butir" tabindex="-1" data-nilai="{{ sprintf('%02d', $j) }}">{{ sprintf('%02d', $j) }}</button>
            @endforeach
        </div>
        <span class="ct-roda-jam__titik">:</span>
        <div class="ct-roda-jam__kolom" data-roda="menit">
            @foreach (range(0, 55, 5) as $m)
                <button type="button" class="ct-roda-jam__butir" tabindex="-1" data-nilai="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</button>
            @endforeach
        </div>
    </div>

    <div class="ct-isian-grup">
        <label class="ct-label" for="{{ $panelId }}-jam">Jam pengingat (24 jam, WIB)</label>
        <input class="ct-isian" type="text" inputmode="numeric" id="{{ $panelId }}-jam" name="remind_time" value="{{ $jamAwal }}"
               placeholder="20:00" maxlength="5" autocomplete="off" required data-isian-jam
               @if ($galat) aria-invalid="true" aria-describedby="{{ $panelId }}-galat" @endif>
        @if ($galat)<p class="ct-galat" id="{{ $panelId }}-galat">{{ $galat }}</p>@endif
    </div>

    <button type="submit" class="ct-tombol ct-tombol--utama pg-form__kirim" data-teks-memuat="Menyimpan...">{{ $pengingat ? 'Simpan Perubahan' : 'Setel Pengingat' }}</button>
</form>
