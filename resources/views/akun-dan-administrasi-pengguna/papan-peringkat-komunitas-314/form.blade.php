{{-- Formulir Filter dan Pengurutan Papan Peringkat (PBI-20 / KF-26) --}}
<div class="ct-peringkat-kontrol" role="region" aria-label="Kontrol Penyaringan Papan Peringkat">
    {{-- Tab Pilihan Periode --}}
    <div class="ct-tablist" role="tablist" aria-label="Periode Papan Peringkat">
        <button type="button" 
                role="tab" 
                class="ct-tab-item {{ $periode === 'minggu' ? 'ct-tab-item--aktif' : '' }}" 
                aria-selected="{{ $periode === 'minggu' ? 'true' : 'false' }}"
                id="tab-minggu"
                onclick="gantiPeriode('minggu')">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width: 15px; height: 15px;">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Mingguan
        </button>
        <button type="button" 
                role="tab" 
                class="ct-tab-item {{ $periode === 'bulan' ? 'ct-tab-item--aktif' : '' }}" 
                aria-selected="{{ $periode === 'bulan' ? 'true' : 'false' }}"
                id="tab-bulan"
                onclick="gantiPeriode('bulan')">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width: 15px; height: 15px;">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            Bulanan
        </button>
        <button type="button" 
                role="tab" 
                class="ct-tab-item {{ $periode === 'semua' ? 'ct-tab-item--aktif' : '' }}" 
                aria-selected="{{ $periode === 'semua' ? 'true' : 'false' }}"
                id="tab-semua"
                onclick="gantiPeriode('semua')">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width: 15px; height: 15px;">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            Sepanjang Waktu
        </button>
    </div>

    {{-- Pemilihan Pengurutan --}}
    <form id="form-pengurutan" method="GET" action="{{ route('peringkat.index') }}" class="ct-urut-grup" onsubmit="return false;">
        <label for="urut-select" class="ct-urut-label">Urutkan Berdasarkan:</label>
        <select id="urut-select" name="urut" class="ct-urut-pilih" onchange="gantiUrutan(this.value)">
            <option value="poin" {{ $urut === 'poin' ? 'selected' : '' }}>Total Poin</option>
            <option value="streak" {{ $urut === 'streak' ? 'selected' : '' }}>Streak Berjalan</option>
            <option value="penurunan" {{ $urut === 'penurunan' ? 'selected' : '' }}>Penurunan Emisi</option>
        </select>
    </form>
</div>
