<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Read: Menampilkan daftar kategori emisi beserta jumlah faktor emisinya.
     */
    public function index()
    {
        $categories = Category::withCount('emissionFactors')
            ->orderBy('id', 'asc')
            ->get();

        return view('master-emisi.categories.index', compact('categories'));
    }

    /**
     * Create (Form): Menampilkan halaman tambah kategori baru.
     */
    public function create()
    {
        return view('master-emisi.categories.create');
    }

    /**
     * Create (Proses): Menyimpan kategori baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100|unique:categories,name',
            'code'      => 'nullable|string|max:50|unique:categories,code',
            'icon'      => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Category::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori emisi berhasil ditambahkan.');
    }

    /**
     * Update (Form): Menampilkan halaman edit kategori.
     */
    public function edit(Category $category)
    {
        return view('master-emisi.categories.edit', compact('category'));
    }

    /**
     * Update (Proses): Memperbarui data kategori emisi.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100|unique:categories,name,' . $category->id,
            'code'      => 'nullable|string|max:50|unique:categories,code,' . $category->id,
            'icon'      => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori emisi berhasil diperbarui.');
    }

    /**
     * Delete: Menghapus kategori jika belum dipakai oleh faktor emisi mana pun.
     */
    public function destroy(Category $category)
    {
        if ($category->emissionFactors()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki data faktor emisi.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori emisi berhasil dihapus.');
    }
}