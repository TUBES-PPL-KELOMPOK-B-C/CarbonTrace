@extends('layouts.aplikasi-111')

@section('judul', 'Tambah Acuan Konversi Porsi')

@section('isi')
<div class="max-w-2xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Tambah Acuan Konversi Porsi</h2>
        <p class="text-sm text-slate-500 mt-1">Tentukan berat dalam gram untuk 1 satuan takaran porsi.</p>
    </div>

    <form action="{{ route('portion-units.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="emission_factor_id" style="color: #1e293b;" class="block text-sm font-semibold mb-1.5">Faktor Emisi Terkait <span class="text-rose-500">*</span></label>
            <select name="emission_factor_id" id="emission_factor_id" required
                    style="color: #0f172a; background-color: #ffffff; border: 1px solid #cbd5e1;"
                    class="w-full rounded-xl px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="" style="color: #0f172a; background-color: #ffffff;">-- Pilih Faktor Emisi --</option>
                @foreach ($emissionFactors as $factor)
                    <option value="{{ $factor->id }}" style="color: #0f172a; background-color: #ffffff;" {{ old('emission_factor_id') == $factor->id ? 'selected' : '' }}>
                        {{ $factor->name }} ({{ $factor->category->name ?? '-' }} - per {{ $factor->unit }})
                    </option>
                @endforeach
            </select>
            @error('emission_factor_id')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" style="color: #1e293b;" class="block text-sm font-semibold mb-1.5">Nama Satuan Porsi <span class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   placeholder="Contoh: 1 Piring Nasi, 1 Potong Ayam Dada, 1 Mangkuk"
                   style="color: #0f172a; background-color: #ffffff; border: 1px solid #cbd5e1;"
                   class="w-full rounded-xl px-3.5 py-2.5 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            @error('name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="gram_per_portion" style="color: #1e293b;" class="block text-sm font-semibold mb-1.5">Berat per Porsi (gram) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" min="0.01" name="gram_per_portion" id="gram_per_portion" value="{{ old('gram_per_portion') }}" required
                   placeholder="Contoh: 150.00"
                   style="color: #0f172a; background-color: #ffffff; border: 1px solid #cbd5e1;"
                   class="w-full rounded-xl px-3.5 py-2.5 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            @error('gram_per_portion')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
            <button type="submit"
                    style="color: #ffffff; background-color: #059669;"
                    class="px-5 py-2.5 hover:bg-emerald-700 text-sm font-semibold rounded-xl transition">
                Simpan Acuan Porsi
            </button>
            <a href="{{ route('conversion-units.index') }}"
               style="color: #1e293b; background-color: #f1f5f9; border: 1px solid #cbd5e1;"
               class="px-5 py-2.5 hover:bg-slate-200 text-sm font-semibold rounded-xl transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection