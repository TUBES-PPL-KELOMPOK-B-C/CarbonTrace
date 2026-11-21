@extends('layouts.aplikasi-111')

@section('judul', 'Manajemen Kategori Emisi')

@section('isi')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Kategori Emisi</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola kelompok aktivitas emisi yang digunakan pada pencatatan harian.</p>
        </div>
        <a href="{{ route('categories.create') }}"
           class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition">
            + Tambah Kategori
        </a>
    </div>

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
                        <th class="py-3.5 px-5">Nama Kategori</th>
                        <th class="py-3.5 px-5">Kode</th>
                        <th class="py-3.5 px-5">Ikon</th>
                        <th class="py-3.5 px-5">Jumlah Faktor Emisi</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($categories as $index => $category)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5 font-medium text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-5 font-semibold text-slate-900">{{ $category->name }}</td>
                            <td class="py-3.5 px-5 font-mono text-xs text-slate-600">{{ $category->code ?? '-' }}</td>
                            <td class="py-3.5 px-5 text-slate-600">{{ $category->icon ?? '-' }}</td>
                            <td class="py-3.5 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $category->emission_factors_count }} faktor
                                </span>
                            </td>
                            <td class="py-3.5 px-5">
                                @if ($category->is_active)
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
                                    <a href="{{ route('categories.edit', $category) }}"
                                       class="px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 transition">
                                        Ubah
                                    </a>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus kategori {{ $category->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-5 text-center text-slate-400">
                                Belum ada data kategori emisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection