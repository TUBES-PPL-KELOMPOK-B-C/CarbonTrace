@extends('layouts.aplikasi-111')

@section('judul', 'Manajemen Faktor Emisi')

@section('isi')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Faktor Emisi</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola nilai koefisien emisi (kg CO₂e per satuan) berdasarkan kategori aktivitas.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('categories.index') }}"
               style="color: #1e293b; background-color: #f1f5f9;"
               class="inline-flex items-center justify-center px-4 py-2.5 hover:bg-slate-200 border border-slate-300 text-sm font-semibold rounded-xl transition">
                ← Kelola Kategori
            </a>
            <a href="{{ route('emission-factors.create') }}"
               style="color: #ffffff; background-color: #059669;"
               class="inline-flex items-center justify-center px-4 py-2.5 hover:bg-emerald-700 text-sm font-semibold rounded-xl transition">
                + Tambah Faktor Emisi
            </a>
        </div>
    </div>

    {{-- Filter per Kategori --}}
    <form method="GET" action="{{ route('emission-factors.index') }}" class="flex flex-wrap items-center gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <label for="category_id" style="color: #334155;" class="text-sm font-semibold">Filter Kategori:</label>
        <select name="category_id" id="category_id" onchange="this.form.submit()"
                style="color: #0f172a; background-color: #ffffff;"
                class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium focus:border-emerald-500 focus:ring-emerald-500">
            <option value="" style="color: #0f172a; background-color: #ffffff;">Semua Kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" style="color: #0f172a; background-color: #ffffff;" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @if (request('category_id'))
            <a href="{{ route('emission-factors.index') }}" style="color: #475569;" class="text-xs font-semibold hover:underline">
                Reset Filter
            </a>
        @endif
    </form>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                        <th class="py-3.5 px-5">No</th>
                        <th class="py-3.5 px-5">Kategori</th>
                        <th class="py-3.5 px-5">Nama Aktivitas / Bahan</th>
                        <th class="py-3.5 px-5">Nilai Faktor</th>
                        <th class="py-3.5 px-5">Satuan</th>
                        <th class="py-3.5 px-5">Sumber Referensi</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($emissionFactors as $index => $factor)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5 font-medium text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $factor->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-semibold text-slate-900">{{ $factor->name }}</td>
                            <td class="py-3.5 px-5 font-mono text-xs font-semibold text-emerald-700">
                                {{ rtrim(rtrim(number_format((float) $factor->factor_value, 4, '.', ''), '0'), '.') }} kg CO₂e
                            </td>
                            <td class="py-3.5 px-5 font-mono text-xs text-slate-600">per {{ $factor->unit }}</td>
                            <td class="py-3.5 px-5 text-xs">
                                @if ($factor->source_url)
                                    <a href="{{ $factor->source_url }}" target="_blank" rel="noopener noreferrer"
                                       class="text-emerald-600 hover:underline font-medium">
                                        Lihat Referensi ↗
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5">
                                @if ($factor->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('emission-factors.edit', $factor) }}"
                                       style="color: #b45309; background-color: #fffbeb;"
                                       class="px-3 py-1.5 text-xs font-semibold hover:bg-amber-100 rounded-lg border border-amber-200 transition">
                                        Ubah
                                    </a>
                                    <form action="{{ route('emission-factors.destroy', $factor) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus faktor emisi {{ $factor->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                style="color: #be123c; background-color: #fff1f2;"
                                                class="px-3 py-1.5 text-xs font-semibold hover:bg-rose-100 rounded-lg border border-rose-200 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 px-5 text-center text-slate-400">
                                Belum ada data faktor emisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection