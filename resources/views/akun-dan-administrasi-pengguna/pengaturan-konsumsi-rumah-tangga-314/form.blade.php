<form method="POST" action="{{ route('rumah.upsert') }}" id="form-rumah-tangga">
    @csrf
    @method('PUT')

    {{-- Kotak Catatan Penjelasan --}}
    <div class="rt-catatan">
        <svg class="ap-ikon" style="color: var(--ct-primary); margin-top: 2px;" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
        </svg>
        <div>
            <strong>Bagaimana angka ini digunakan dalam kalkulasi?</strong>
            <p style="margin: 4px 0 0 0;">
                Data ini menjadi dasar pembagi emisi listrik rumahmu di seluruh sistem. Misalnya daya listrik rumah dipakai bersama 4 orang penghuni, maka emisi listrik yang tercatat dibagi 4 (masing-masing 25%).
            </p>
            <p style="margin: 4px 0 0 0; color: var(--ct-accent); font-size: 13px;">
                * Cukup ubah isian di bawah ini lalu tekan <strong>Simpan Pengaturan</strong> untuk memperbarui data kapan saja (tidak perlu menghapus data lama).
            </p>
        </div>
    </div>

    {{-- 1. Penggeser Jumlah Penghuni (1 - 20) --}}
    <div class="ct-isian-grup" style="margin-bottom: var(--ct-s5);">
        <label for="household_size" class="ct-label">
            Jumlah Penghuni Rumah <span class="text-rose-500">*</span>
        </label>
        <div class="rt-penggeser-baris">
            <input type="range"
                   id="household_size"
                   name="household_size"
                   min="1"
                   max="20"
                   step="1"
                   value="{{ old('household_size', $setting->household_size ?? 1) }}"
                   class="ct-penggeser"
                   oninput="perbaruiKalkulasiRt(this.value, document.getElementById('power_watt').value)"
                   aria-describedby="ket-penghuni @error('household_size') galat-penghuni @enderror"
                   @error('household_size') aria-invalid="true" @enderror>
            <span class="rt-penggeser-angka" id="angka-penghuni">
                {{ old('household_size', $setting->household_size ?? 1) }} orang
            </span>
        </div>
        <span class="text-sm" id="ket-penghuni" style="color: var(--ct-text-muted); font-size: 13px;">
            Geser atau gunakan tombol panah keyboard (1 sampai 20 orang).
        </span>
        @error('household_size')
            <span class="ct-galat" id="galat-penghuni" role="alert">{{ $message }}</span>
        @enderror
    </div>

    {{-- 2. Kolom Daya Listrik Terpasang (Watt) --}}
    <div class="ct-isian-grup" style="margin-bottom: var(--ct-s5);">
        <label for="power_watt" class="ct-label">
            Daya Listrik Terpasang Rumah (PLN) <span class="text-rose-500">*</span>
        </label>
        <div class="ct-isian-satuan">
            <input type="number"
                   id="power_watt"
                   name="power_watt"
                   min="450"
                   max="50000"
                   step="50"
                   placeholder="Contoh: 900, 1300, 2200, 4400"
                   value="{{ old('power_watt', $setting->power_watt ?? 900) }}"
                   class="ct-isian @error('power_watt') border-rose-500 @enderror"
                   required
                   oninput="perbaruiKalkulasiRt(document.getElementById('household_size').value, this.value)"
                   aria-describedby="ket-watt @error('power_watt') galat-watt @enderror"
                   @error('power_watt') aria-invalid="true" @enderror>
            <span>watt</span>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px;">
            <span style="font-size: 12px; color: var(--ct-text-muted); align-self: center; margin-right: 4px;">Pilihan standar PLN:</span>
            @foreach ([450, 900, 1300, 2200, 3500, 4400, 5500] as $daya)
                <button type="button" 
                        onclick="const pw = document.getElementById('power_watt'); pw.value = {{ $daya }}; perbaruiKalkulasiRt(document.getElementById('household_size').value, {{ $daya }});" 
                        class="ct-pelat ct-pelat--netral" 
                        style="cursor: pointer; font-size: 11px; padding: 2px 8px; background: var(--ct-surface); border: 1px solid var(--ct-border); color: var(--ct-text-muted);"
                        onmouseover="this.style.borderColor='var(--ct-primary)'; this.style.color='var(--ct-primary)';"
                        onmouseout="this.style.borderColor='var(--ct-border)'; this.style.color='var(--ct-text-muted)';">
                    {{ number_format($daya, 0, ',', '.') }} VA
                </button>
            @endforeach
        </div>
        <span class="text-sm" id="ket-watt" style="color: var(--ct-text-muted); font-size: 13px;">
            Daya listrik meteran rumah (antara 450 sampai 50.000 watt).
        </span>
        @error('power_watt')
            <span class="ct-galat" id="galat-watt" role="alert">{{ $message }}</span>
        @enderror
    </div>

    {{-- 3. Kartu Pilihan Kepemilikan Kendaraan (4 Kartu Radio) --}}
    <div class="ct-isian-grup" style="margin-bottom: var(--ct-s5);">
        <span class="ct-label">
            Kepemilikan Kendaraan Rumah Tangga <span class="text-rose-500">*</span>
        </span>
        <div class="rt-kartu-pilihan-kisi">
            @php
                $pilihanKendaraan = [
                    'tidak ada' => [
                        'label' => 'Tidak Ada',
                        'ikon' => '<path d="M13 4v6m0 0v8m0-8h4m-4 0H9M9 4a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm8 16a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>',
                    ],
                    'motor' => [
                        'label' => 'Sepeda Motor',
                        'ikon' => '<circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6h-3l-3 5h5l3.5-3.5H21M9 11l-3.5 6.5M15 11l3.5 6.5"/>',
                    ],
                    'mobil' => [
                        'label' => 'Mobil',
                        'ikon' => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.5C1.4 11.2 1 12 1 13v3c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
                    ],
                    'keduanya' => [
                        'label' => 'Keduanya',
                        'ikon' => '<circle cx="5" cy="18" r="2.5"/><circle cx="19" cy="18" r="2.5"/><path d="M12 7h-2l-2 4h4l2-2h3M7.5 11 5 15.5M12.5 11 15 15.5M10 18h4"/>',
                    ],
                ];
                $terpilih = old('vehicle_ownership', $setting->vehicle_ownership ?? 'tidak ada');
            @endphp

            @foreach ($pilihanKendaraan as $kunci => $info)
                <label class="ct-pilihan-kartu">
                    <input type="radio"
                           name="vehicle_ownership"
                           value="{{ $kunci }}"
                           @checked($terpilih === $kunci)
                           onchange="document.getElementById('simulasi-kendaraan').textContent = '{{ $info['label'] }}'"
                           required>
                    <svg class="ap-ikon ct-pilihan-kartu__ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        {!! $info['ikon'] !!}
                    </svg>
                    <span class="ct-pilihan-kartu__label">{{ $info['label'] }}</span>
                </label>
            @endforeach
        </div>
        @error('vehicle_ownership')
            <span class="ct-galat" role="alert">{{ $message }}</span>
        @enderror
    </div>

    {{-- Kartu Simulasi & Pratinjau Interaktif --}}
    <div class="ct-kartu" style="background: color-mix(in srgb, var(--ct-primary) 6%, var(--ct-surface)); border: 1px dashed var(--ct-primary); margin-bottom: var(--ct-s5); padding: var(--ct-s4);">
        <h4 style="font-size: 15px; font-weight: 700; margin: 0 0 var(--ct-s2); color: var(--ct-accent); display: flex; align-items: center; gap: 8px;">
            <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Simulasi Beban Emisi Rumah Tangga Anda
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--ct-s3); font-size: 13px;">
            <div style="background: var(--ct-surface-raised); padding: var(--ct-s3); border-radius: var(--ct-r-ctrl); border: 1px solid var(--ct-border);">
                <div style="color: var(--ct-text-muted);">Beban Per Kapita:</div>
                <div id="simulasi-beban" style="font-size: 18px; font-weight: 800; color: var(--ct-primary); margin-top: 2px;">
                    {{ number_format(100 / max(1, old('household_size', $setting->household_size ?? 1)), 1) }}%
                </div>
                <div style="color: var(--ct-text-muted); font-size: 11px;">(1 dari <span id="simulasi-orang">{{ old('household_size', $setting->household_size ?? 1) }}</span> orang)</div>
            </div>
            <div style="background: var(--ct-surface-raised); padding: var(--ct-s3); border-radius: var(--ct-r-ctrl); border: 1px solid var(--ct-border);">
                <div style="color: var(--ct-text-muted);">Kapasitas Daya Rumah:</div>
                <div id="simulasi-watt" style="font-size: 18px; font-weight: 800; color: var(--ct-accent); margin-top: 2px;">
                    {{ number_format(old('power_watt', $setting->power_watt ?? 900), 0, ',', '.') }} VA
                </div>
                <div style="color: var(--ct-text-muted); font-size: 11px;">Standar Golongan PLN</div>
            </div>
            <div style="background: var(--ct-surface-raised); padding: var(--ct-s3); border-radius: var(--ct-r-ctrl); border: 1px solid var(--ct-border);">
                <div style="color: var(--ct-text-muted);">Aset Kendaraan:</div>
                <div id="simulasi-kendaraan" style="font-size: 16px; font-weight: 700; color: var(--ct-text); margin-top: 4px; text-transform: capitalize;">
                    {{ $terpilih === 'tidak ada' ? 'Tidak Ada' : ($terpilih === 'motor' ? 'Sepeda Motor' : ($terpilih === 'mobil' ? 'Mobil' : 'Keduanya')) }}
                </div>
                <div style="color: var(--ct-text-muted); font-size: 11px;">Profil mobilitas</div>
            </div>
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="rt-aksi-tombol">
        <div>
            @if ($setting)
                <x-tombol tipe="button" varian="hantu" onclick="bukaModal('modal-hapus-rumah')" style="color: var(--ct-danger);">
                    <svg class="ap-ikon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                    Hapus Data Rumah Tangga
                </x-tombol>
            @endif
        </div>

        <div style="display: flex; gap: var(--ct-s3);">
            <x-tombol href="{{ route('profil.index') }}" varian="sekunder">
                Kembali ke Profil
            </x-tombol>

            <x-tombol tipe="submit" varian="utama" id="tombol-simpan-rumah">
                Simpan & Perbarui Pengaturan
            </x-tombol>
        </div>
    </div>
</form>

<script>
    function perbaruiKalkulasiRt(penghuni, watt) {
        penghuni = parseInt(penghuni) || 1;
        watt = parseInt(watt) || 0;

        const angkaEl = document.getElementById('angka-penghuni');
        if (angkaEl) angkaEl.textContent = penghuni + ' orang';

        const orangEl = document.getElementById('simulasi-orang');
        if (orangEl) orangEl.textContent = penghuni;

        const bebanEl = document.getElementById('simulasi-beban');
        if (bebanEl) {
            const persen = (100 / penghuni).toFixed(1);
            bebanEl.textContent = persen + '%';
        }

        const wattEl = document.getElementById('simulasi-watt');
        if (wattEl) {
            wattEl.textContent = watt.toLocaleString('id-ID') + ' VA';
        }
    }
</script>
