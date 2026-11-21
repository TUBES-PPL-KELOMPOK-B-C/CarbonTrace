<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\EmissionFactor;
use Illuminate\Http\Request;

class EmissionFactorController extends Controller
{
    /**
     * Read: Menampilkan daftar faktor emisi beserta kategori dan jumlah acuan konversinya.
     */
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $emissionFactors = EmissionFactor::with('category')
            ->withCount(['appliances', 'portionUnits'])
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        return view('master-emisi.emission-factors.index', compact('emissionFactors', 'categories'));
    }

    /**
     * Create (Form): Menampilkan halaman tambah faktor emisi baru.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('master-emisi.emission-factors.create', compact('categories'));
    }

    /**
     * Create (Proses): Menyimpan faktor emisi baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:150',
            'factor_value' => 'required|numeric|min:0',
            'unit'         => 'required|string|max:50',
            'source_url'   => 'nullable|url|max:255',
            'is_active'    => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        EmissionFactor::create($validated);

        return redirect()
            ->route('emission-factors.index')
            ->with('success', 'Faktor emisi berhasil ditambahkan.');
    }

    /**
     * Update (Form): Menampilkan halaman ubah faktor emisi.
     */
    public function edit(EmissionFactor $emissionFactor)
    {
        $categories = Category::orderBy('name')->get();

        return view('master-emisi.emission-factors.edit', compact('emissionFactor', 'categories'));
    }

    /**
     * Update (Proses): Memperbarui data faktor emisi.
     */
    public function update(Request $request, EmissionFactor $emissionFactor)
    {
        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:150',
            'factor_value' => 'required|numeric|min:0',
            'unit'         => 'required|string|max:50',
            'source_url'   => 'nullable|url|max:255',
            'is_active'    => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $emissionFactor->update($validated);

        return redirect()
            ->route('emission-factors.index')
            ->with('success', 'Faktor emisi berhasil diperbarui.');
    }

    /**
     * Delete: Menghapus faktor emisi jika belum dipakai oleh acuan konversi satuan.
     */
    public function destroy(EmissionFactor $emissionFactor)
    {
        if ($emissionFactor->appliances()->exists() || $emissionFactor->portionUnits()->exists()) {
            return redirect()
                ->route('emission-factors.index')
                ->with('error', 'Faktor emisi tidak dapat dihapus karena masih terhubung dengan data acuan konversi satuan.');
        }

        $emissionFactor->delete();

        return redirect()
            ->route('emission-factors.index')
            ->with('success', 'Faktor emisi berhasil dihapus.');
    }
}