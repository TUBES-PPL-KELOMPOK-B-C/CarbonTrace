@extends('layouts.aplikasi-111')

@section('judul', 'Tambah Kategori Emisi')

@section('isi')
<div class="max-w-2xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Tambah Kategori Emisi Baru</h2>
        <p class="text-sm text-slate-500 mt-1">Isi detail kategori aktivitas yang akan tersedia bagi pengguna.</p>
    </div>

    <form action="{{ route('categories.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   placeholder="Contoh: Transportasi"
                   class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            @error('name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="code" class="block text-sm font-semibold text-slate-700 mb-1">Kode Kategori</label>
            <input type="text" name="code" id="code" value="{{ old('code') }}"
                   placeholder="Contoh: TRANSPORT"
                   class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            @error('code')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="icon" class="block text-sm font-semibold text-slate-700 mb-1">Nama / Kelas Ikon</label>
            <input type="text" name="icon" id="icon" value="{{ old('icon') }}"
                   placeholder="Contoh: car atau bolt"
                   class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            @error('icon')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <label for="is_active" class="text-sm font-medium text-slate-700">Aktifkan kategori ini</label>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
            <button type="submit"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition">
                Simpan Kategori
            </button>
            <a href="{{ route('categories.index') }}"
               class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection