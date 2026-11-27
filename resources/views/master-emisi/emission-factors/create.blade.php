@extends('layouts.aplikasi-111')

@section('judul', 'Tambah Faktor Emisi')

@section('isi')
<div class="max-w-2xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Tambah Faktor Emisi Baru</h2>
        <p class="text-sm text-slate-500 mt-1">Masukkan nilai koefisien emisi dan satuan baku perhitungannya.</p>
    </div>

    <form action="{{ route('emission-factors.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="category_id" style="color: #1e293b;" class="block text-sm font-semibold mb-1.5">Kategori Emisi <span class="text-rose-500">*</span></label>
            <select name="category_id" id="category_id" required
                    style="color: #0f172a; background-color: #ffffff; border: 1px solid #cbd5e1;"
                    class="w-full rounded-xl px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="" style="color: #0f172a; background-color: #ffffff;">-- Pilih Kategori --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" style="color: #0f172a; background-color: #ffffff;" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" style="color: #1e293b;" class="block text-sm font-semibold mb-1.5">Nama Aktivitas / Jenis Emisi <span class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   placeholder="Contoh: Motor Bensin, Listrik PLN, Daging Sapi"
                   style="color: #0f172a; background-color: #ffffff; border: 1px solid #cbd5e1;"
                   class="w-full rounded-xl px-3.5 py-2.5 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            @error('name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="factor_value" style="color: #1e293b;" class="block text-sm font-semibold mb-1.5">Nilai Faktor (kg CO₂e) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.0001" min="0" name="factor_value" id="factor_value" value="{{ old('factor_value') }}" required
                       placeholder="Contoh: 0.1030"
                       style="color: #0f172a; background-color: #ffffff; border: 1px solid #cbd5e1;"
                       class="w-full rounded-xl px-3.5 py-2.5 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                @error('factor_value')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="unit" style="color: #1e293b;" class="block text-sm font-semibold mb-1.5">Satuan Baku <span class="text-rose-500">*</span></label>
                <input type="text" name="unit" id="unit" value="{{ old('unit') }}" required
                       placeholder="Contoh: km, kWh, kg, liter"
                       style="color: #0f172a; background-color: #ffffff; border: 1px solid #cbd5e1;"
                       class="w-full rounded-xl px-3.5 py-2.5 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                @error('unit')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="source_url" style="color: #1e293b;" class="block text-sm font-semibold mb-1.5">Tautan Sumber Referensi (Opsional)</label>
            <input type="url" name="source_url" id="source_url" value="{{ old('source_url') }}"
                   placeholder="https://..."
                   style="color: #0f172a; background-color: #ffffff; border: 1px solid #cbd5e1;"
                   class="w-full rounded-xl px-3.5 py-2.5 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            @error('source_url')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <label for="is_active" style="color: #334155;" class="text-sm font-medium">Aktifkan faktor emisi ini</label>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
            <button type="submit"
                    style="color: #ffffff; background-color: #059669;"
                    class="px-5 py-2.5 hover:bg-emerald-700 text-sm font-semibold rounded-xl transition">
                Simpan Faktor Emisi
            </button>
            <a href="{{ route('emission-factors.index') }}"
               style="color: #1e293b; background-color: #f1f5f9; border: 1px solid #cbd5e1;"
               class="px-5 py-2.5 hover:bg-slate-200 text-sm font-semibold rounded-xl transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection