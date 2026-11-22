@extends('layouts.aplikasi-111')

@section('judul', 'Manajemen Acuan Konversi Satuan')

@section('isi')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Acuan Konversi Satuan (PBI-09)</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola acuan konversi satuan porsi makanan (gram) dan daya perangkat listrik (Watt) ke satuan baku.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('emission-factors.index') }}"
               style="color: #1e293b; background-color: #f1f5f9; border: 1px solid #cbd5e1;"
               class="inline-flex items-center justify-center px-4 py-2.5 hover:bg-slate-200 text-sm font-semibold rounded-xl transition">
                ← Faktor Emisi
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    {{-- Bagian 1: Acuan Konversi Satuan Porsi Makanan --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">1. Acuan Konversi Porsi Makanan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Konversi takaran porsi (piring, mangkuk, potong) ke satuan gram.</p>
            </div>
            <a href="{{ route('portion-units.create') }}"
               style="color: #ffffff; background-color: #059669;"
               class="inline-flex items-center justify-center px-4 py-2 hover:bg-emerald-700 text-xs font-semibold rounded-xl transition">
                + Tambah Acuan Porsi
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                        <th class="py-3 px-5">No</th>
                        <th class="py-3 px-5">Nama Takaran Porsi</th>
                        <th class="py-3 px-5">Faktor Emisi Terkait</th>
                        <th class="py-3 px-5">Berat per Porsi</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($portionUnits as $index => $portion)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-5 font-medium text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-3 px-5 font-semibold text-slate-900">{{ $portion->name }}</td>
                            <td class="py-3 px-5 text-xs text-slate-600">{{ $portion->emissionFactor->name ?? '-' }}</td>
                            <td class="py-3 px-5 font-mono text-xs font-semibold text-emerald-700">
                                {{ number_format((float) $portion->gram_per_portion, 2, '.', '') }} gram
                            </td>
                            <td class="py-3 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('portion-units.edit', $portion) }}"
                                       style="color: #b45309; background-color: #fffbeb;"
                                       class="px-3 py-1.5 text-xs font-semibold hover:bg-amber-100 rounded-lg border border-amber-200 transition">
                                        Ubah
                                    </a>
                                    <form action="{{ route('portion-units.destroy', $portion) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus acuan porsi {{ $portion->name }}?')">
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
                            <td colspan="5" class="py-6 px-5 text-center text-slate-400">
                                Belum ada data acuan konversi porsi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bagian 2: Acuan Konversi Daya Perangkat Listrik --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">2. Acuan Daya Perangkat Listrik</h3>
                <p class="text-xs text-slate-500 mt-0.5">Konversi pemakaian perangkat elektronik (Watt × jam) ke satuan kWh.</p>
            </div>
            <a href="{{ route('appliances.create') }}"
               style="color: #ffffff; background-color: #059669;"
               class="inline-flex items-center justify-center px-4 py-2 hover:bg-emerald-700 text-xs font-semibold rounded-xl transition">
                + Tambah Perangkat Listrik
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                        <th class="py-3 px-5">No</th>
                        <th class="py-3 px-5">Nama Perangkat Listrik</th>
                        <th class="py-3 px-5">Faktor Emisi Terkait</th>
                        <th class="py-3 px-5">Daya Listrik</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($appliances as $index => $appliance)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-5 font-medium text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-3 px-5 font-semibold text-slate-900">{{ $appliance->name }}</td>
                            <td class="py-3 px-5 text-xs text-slate-600">{{ $appliance->emissionFactor->name ?? '-' }}</td>
                            <td class="py-3 px-5 font-mono text-xs font-semibold text-emerald-700">
                                {{ number_format((float) $appliance->wattage, 2, '.', '') }} Watt
                            </td>
                            <td class="py-3 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('appliances.edit', $appliance) }}"
                                       style="color: #b45309; background-color: #fffbeb;"
                                       class="px-3 py-1.5 text-xs font-semibold hover:bg-amber-100 rounded-lg border border-amber-200 transition">
                                        Ubah
                                    </a>
                                    <form action="{{ route('appliances.destroy', $appliance) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus perangkat {{ $appliance->name }}?')">
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
                            <td colspan="5" class="py-6 px-5 text-center text-slate-400">
                                Belum ada data acuan daya perangkat listrik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection