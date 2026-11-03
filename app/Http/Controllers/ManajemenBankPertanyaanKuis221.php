<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanManajemenBankPertanyaanKuis221;
use App\Models\QuizQuestion221;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManajemenBankPertanyaanKuis221 extends Controller
{
    use AuthorizesRequests;

    /**
     * Tampilkan daftar bank pertanyaan kuis emisi (Read).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', QuizQuestion221::class);

        $query = QuizQuestion221::query();

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $pertanyaan = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $totalAktif = QuizQuestion221::where('is_active', true)->count();
        $totalSemua = QuizQuestion221::count();

        return view('pencatatan-aktivitas.manajemen-bank-pertanyaan-kuis-221.index', [
            'pertanyaan' => $pertanyaan,
            'totalAktif' => $totalAktif,
            'totalSemua' => $totalSemua,
        ]);
    }

    /**
     * Tampilkan formulir pembuatan pertanyaan baru (Create).
     */
    public function create(): View
    {
        $this->authorize('create', QuizQuestion221::class);

        return view('pencatatan-aktivitas.manajemen-bank-pertanyaan-kuis-221.form', [
            'soal' => new QuizQuestion221([
                'options' => [
                    ['label' => '', 'weight' => 0],
                    ['label' => '', 'weight' => 0],
                ],
                'emission_weight' => 1.0,
                'is_active' => true,
            ]),
            'aksi' => route('kuis.store'),
            'metode' => 'POST',
            'judul' => 'Tambah Pertanyaan Kuis',
        ]);
    }

    /**
     * Simpan pertanyaan kuis baru ke basis data (Create).
     */
    public function store(SimpanManajemenBankPertanyaanKuis221 $request): RedirectResponse
    {
        $this->authorize('create', QuizQuestion221::class);

        $validated = $request->validated();

        QuizQuestion221::create([
            'question' => $validated['question'],
            'options' => array_values($validated['options']),
            'emission_weight' => $validated['emission_weight'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('kuis.index')
            ->with('sukses', 'Pertanyaan tersimpan.');
    }

    /**
     * Tampilkan formulir sunting pertanyaan kuis (Edit).
     */
    public function edit(QuizQuestion221 $soal): View
    {
        $this->authorize('update', $soal);

        return view('pencatatan-aktivitas.manajemen-bank-pertanyaan-kuis-221.form', [
            'soal' => $soal,
            'aksi' => route('kuis.update', $soal),
            'metode' => 'PUT',
            'judul' => 'Ubah Pertanyaan Kuis',
        ]);
    }

    /**
     * Perbarui data pertanyaan kuis (Update).
     */
    public function update(SimpanManajemenBankPertanyaanKuis221 $request, QuizQuestion221 $soal): RedirectResponse
    {
        $this->authorize('update', $soal);

        $validated = $request->validated();

        $soal->update([
            'question' => $validated['question'],
            'options' => array_values($validated['options']),
            'emission_weight' => $validated['emission_weight'],
            'is_active' => $request->boolean('is_active', $soal->is_active),
        ]);

        return redirect()->route('kuis.index')
            ->with('sukses', 'Pertanyaan berhasil diperbarui.');
    }

    /**
     * Beralih status aktif / nonaktif pertanyaan (Patch).
     */
    public function status(QuizQuestion221 $soal): RedirectResponse
    {
        $this->authorize('update', $soal);

        $soal->update(['is_active' => !$soal->is_active]);

        $statusTeks = $soal->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('kuis.index')
            ->with('sukses', "Pertanyaan berhasil {$statusTeks}.");
    }

    /**
     * Hapus pertanyaan kuis permanen (hanya yang belum pernah dijawab).
     */
    public function destroy(QuizQuestion221 $soal): RedirectResponse
    {
        $this->authorize('delete', $soal);

        if ($soal->hasBeenAnswered()) {
            return redirect()->route('kuis.index')
                ->with('galat', 'Pertanyaan yang sudah dijawab tidak dapat dihapus permanen.');
        }

        $soal->delete();

        return redirect()->route('kuis.index')
            ->with('sukses', 'Pertanyaan berhasil dihapus.');
    }
}
