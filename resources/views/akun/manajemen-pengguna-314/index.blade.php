@extends('layouts.aplikasi-111')

@section('judul', 'Manajemen Pengguna')

@push('aset')
    <link rel="stylesheet" href="{{ asset('build/assets/profil-314.css') }}">
    <style>
        .ct-admin-filter {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            background: var(--ct-surface);
            border: 1px solid var(--ct-border);
            padding: 14px 18px;
            border-radius: var(--ct-r-card);
            margin-bottom: 20px;
        }
        .ct-admin-cari {
            flex: 1;
            min-width: 220px;
            position: relative;
        }
        .ct-admin-tabel-aksi {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ct-saklar-btn {
            padding: 6px 12px;
            border-radius: var(--ct-r-ctrl);
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--ct-border);
            cursor: pointer;
            transition: all 0.2s;
        }
        .ct-saklar-btn--aktif {
            background: rgba(244, 63, 94, 0.15);
            color: #F43F5E;
            border-color: rgba(244, 63, 94, 0.3);
        }
        .ct-saklar-btn--aktif:hover {
            background: rgba(244, 63, 94, 0.25);
        }
        .ct-saklar-btn--nonaktif {
            background: rgba(16, 185, 129, 0.15);
            color: #10B981;
            border-color: rgba(16, 185, 129, 0.3);
        }
        .ct-saklar-btn--nonaktif:hover {
            background: rgba(16, 185, 129, 0.25);
        }
    </style>
@endpush

@section('isi')
<div class="ct-profil-wadah">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 26px; font-weight: 800; color: var(--ct-text); margin: 0;">Manajemen Pengguna & Peran</h2>
            <p style="font-size: 14px; color: var(--ct-text-muted); margin: 4px 0 0 0;">
                Kelola hak akses, penugasan peran administrator, dan status keaktifan akun pengguna.
            </p>
        </div>
        <button type="button" class="ct-tombol ct-tombol--utama" onclick="bukaModal('modal-tambah-pengguna')">
            <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            Tambah Akun Baru
        </button>
    </div>

    {{-- Pesan Notifikasi Sukses / Galat --}}
    @if (session('sukses'))
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #10B981; padding: 12px 18px; border-radius: var(--ct-r-ctrl); margin-bottom: 16px; font-size: 14px; font-weight: 600;">
            ✓ {{ session('sukses') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.4); color: #F43F5E; padding: 12px 18px; border-radius: var(--ct-r-ctrl); margin-bottom: 16px; font-size: 14px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Bilah Pencarian & Penyaring --}}
    <form method="GET" action="{{ route('pengguna.index') }}" class="ct-admin-filter">
        <div class="ct-admin-cari">
            <input type="text" 
                   name="cari" 
                   value="{{ $cari }}" 
                   class="ct-isian" 
                   placeholder="Cari nama atau surel..."
                   style="height: 42px;">
        </div>

        <select name="peran" class="ct-isian" style="height: 42px; width: auto; min-width: 150px;">
            <option value="">Semua Peran</option>
            @foreach ($semuaPeran as $peran)
                <option value="{{ $peran->id }}" {{ $peranId == $peran->id ? 'selected' : '' }}>
                    {{ ucfirst($peran->name) }}
                </option>
            @endforeach
        </select>

        <select name="status" class="ct-isian" style="height: 42px; width: auto; min-width: 140px;">
            <option value="">Semua Status</option>
            <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ $status === '0' ? 'selected' : '' }}>Nonaktif / Diblokir</option>
        </select>

        <button type="submit" class="ct-tombol ct-tombol--sekunder" style="height: 42px; padding: 0 16px;">
            Saring
        </button>

        @if ($cari || $peranId || $status !== null)
            <a href="{{ route('pengguna.index') }}" class="ct-tombol ct-tombol--hantu" style="height: 42px;">
                Reset
            </a>
        @endif
    </form>

    {{-- Tabel Pengguna --}}
    @if ($daftarPengguna->isEmpty())
        <div class="ct-kartu" style="text-align: center; padding: 48px 24px;">
            <svg style="width: 48px; height: 48px; margin: 0 auto 12px; color: var(--ct-text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--ct-text); margin: 0 0 6px 0;">Tidak Ada Pengguna Ditemukan</h3>
            <p style="font-size: 14px; color: var(--ct-text-muted); margin: 0 0 16px 0;">
                Tidak ada pengguna yang cocok dengan kata kunci atau filter pencarian itu.
            </p>
            <a href="{{ route('pengguna.index') }}" class="ct-tombol ct-tombol--utama">
                Bersihkan Pencarian
            </a>
        </div>
    @else
        <div class="ct-kartu" style="padding: 0; overflow: hidden;">
            <table class="ct-tabel" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Pengguna</th>
                        <th>Peran Akun</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftarPengguna as $p)
                        @php $adalahSaya = (int)$p->id === (int)auth()->id(); @endphp
                        <tr style="{{ $adalahSaya ? 'background: rgba(16, 185, 129, 0.05);' : '' }}">
                            <td data-label="ID" style="color: var(--ct-text-muted); font-size: 13px;">
                                #{{ $p->id }}
                            </td>
                            <td data-label="Pengguna">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="ct-avatar" style="width: 36px; height: 36px; font-size: 14px;">
                                        {{ \Illuminate\Support\Str::upper(substr($p->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--ct-text);">
                                            {{ $p->name }}
                                            @if ($adalahSaya)
                                                <span class="ct-pelat ct-pelat--aman" style="font-size: 10px; margin-left: 6px;">Kamu</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 12px; color: var(--ct-text-muted);">{{ $p->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Peran">
                                @if ($adalahSaya)
                                    <span class="ct-pelat ct-pelat--aman" style="font-weight: 700;">
                                        {{ ucfirst($p->role?->name ?? 'Administrator') }}
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('pengguna.peran', $p->id) }}" style="display: inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role_id" onchange="this.form.submit()" class="ct-isian" style="height: 34px; padding: 0 10px; font-size: 13px; width: auto;">
                                            @foreach ($semuaPeran as $peran)
                                                <option value="{{ $peran->id }}" {{ $p->role_id == $peran->id ? 'selected' : '' }}>
                                                    {{ ucfirst($peran->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                            <td data-label="Status">
                                @if ($p->is_active)
                                    <span class="ct-pelat ct-pelat--aman">Aktif</span>
                                @else
                                    <span class="ct-pelat ct-pelat--terlampaui">Nonaktif</span>
                                @endif
                            </td>
                            <td data-label="Terdaftar" style="font-size: 13px; color: var(--ct-text-muted);">
                                {{ $p->created_at ? $p->created_at->format('d M Y') : '-' }}
                            </td>
                            <td data-label="Aksi" style="text-align: right;">
                                @if ($adalahSaya)
                                    <span style="font-size: 12px; color: var(--ct-text-muted); font-style: italic;">Akun Saat Ini</span>
                                @else
                                    <form method="POST" action="{{ route('pengguna.status', $p->id) }}" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun {{ $p->name }}?');">
                                        @csrf
                                        @method('PATCH')
                                        @if ($p->is_active)
                                            <button type="submit" class="ct-saklar-btn ct-saklar-btn--aktif" title="Nonaktifkan Akun">
                                                Nonaktifkan
                                            </button>
                                        @else
                                            <button type="submit" class="ct-saklar-btn ct-saklar-btn--nonaktif" title="Aktifkan Kembali">
                                                Aktifkan
                                            </button>
                                        @endif
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginasi --}}
        <div style="margin-top: 16px;">
            {{ $daftarPengguna->links() }}
        </div>
    @endif
</div>

{{-- Modal Tambah Pengguna --}}
<div id="modal-tambah-pengguna" class="ct-latar-belakang hidden" role="dialog" aria-modal="true">
    <div class="ct-modal" style="max-width: 500px;">
        <div class="ct-modal__kepala">
            <h3 class="ct-modal__judul">Tambah Akun Pengguna</h3>
            <button type="button" class="ct-modal__tutup" onclick="tutupModal('modal-tambah-pengguna')">&times;</button>
        </div>
        <form method="POST" action="{{ route('pengguna.store') }}">
            @csrf
            <div class="ct-modal__isi" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="ct-isian-grup">
                    <label class="ct-label" for="tambah-nama">Nama Lengkap *</label>
                    <input type="text" id="tambah-nama" name="name" class="ct-isian" required placeholder="Contoh: Budi Prasetyo">
                </div>

                <div class="ct-isian-grup">
                    <label class="ct-label" for="tambah-email">Surel / Email *</label>
                    <input type="email" id="tambah-email" name="email" class="ct-isian" required placeholder="user@carbontrace.id">
                </div>

                <div class="ct-isian-grup">
                    <label class="ct-label" for="tambah-password">Kata Sandi Awal *</label>
                    <input type="password" id="tambah-password" name="password" class="ct-isian" required placeholder="Minimal 8 karakter">
                </div>

                <div class="ct-isian-grup">
                    <label class="ct-label" for="tambah-peran">Peran Akun *</label>
                    <select id="tambah-peran" name="role_id" class="ct-isian" required>
                        @foreach ($semuaPeran as $peran)
                            <option value="{{ $peran->id }}">{{ ucfirst($peran->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="ct-modal__kaki">
                <button type="button" class="ct-tombol ct-tombol--sekunder" onclick="tutupModal('modal-tambah-pengguna')">Batal</button>
                <button type="submit" class="ct-tombol ct-tombol--utama">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }
    function tutupModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }
</script>
@endsection
