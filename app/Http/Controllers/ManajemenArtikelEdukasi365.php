<?php

namespace App\Http\Controllers;

use App\Models\Article365;
use App\Models\ArticleCategorie365;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManajemenArtikelEdukasi365 extends Controller
{
    public function index()
    {
        $articles = Article365::with('category')
            ->latest()
            ->paginate(10);

        return view(
            'edukasi-dan-komunitas.manajemen-artikel-edukasi-365.index',
            compact('articles')
        );
    }

    public function create()
    {
        $categories = ArticleCategorie365::all();

        return view(
            'edukasi-dan-komunitas.manajemen-artikel-edukasi-365.form',
            compact('categories')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_category_id' => 'required|exists:article_categories,id',
            'title' => 'required|string|max:160',
            'summary' => 'required|string|max:300',
            'content' => 'required|string|min:200',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:draf,terbit,dicabut',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['title'])
            . '-' . Str::lower(Str::random(6));

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')
                ->store('artikel', 'public');
        }

        if ($validated['status'] === 'terbit') {
            $validated['published_at'] = now();
        }

        Article365::create($validated);

        return redirect()
            ->route('artikel-edukasi.index')
            ->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article365 $article)
    {
        $categories = ArticleCategorie365::all();

        return view(
            'edukasi-dan-komunitas.manajemen-artikel-edukasi-365.form',
            compact('article', 'categories')
        );
    }

    public function update(Request $request, Article365 $article)
    {
        $validated = $request->validate([
            'article_category_id' => 'required|exists:article_categories,id',
            'title' => 'required|string|max:160',
            'summary' => 'required|string|max:300',
            'content' => 'required|string|min:200',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:draf,terbit,dicabut',
        ]);

        $validated['slug'] = Str::slug($validated['title'])
            . '-' . Str::lower(Str::random(6));

        if ($request->hasFile('cover_image')) {
            if ($article->cover_image) {
                Storage::disk('public')->delete($article->cover_image);
            }

            $validated['cover_image'] = $request->file('cover_image')
                ->store('artikel', 'public');
        }

        if ($validated['status'] === 'terbit' && !$article->published_at) {
            $validated['published_at'] = now();
        } else {
            unset($validated['published_at']);
        }

        $article->update($validated);

        return redirect()
            ->route('artikel-edukasi.index')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article365 $article)
    {
        if ($article->cover_image) {
            Storage::disk('public')->delete($article->cover_image);
        }

        $article->delete();

        return redirect()
            ->route('artikel-edukasi.index')
            ->with('success', 'Artikel berhasil dihapus.');
    }
}
