<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanTemplateAktivitasRutin221;
use App\Models\Activitie221;
use App\Models\ActivityTemplate221;
use App\Models\Category;
use App\Models\EmissionFactor;
use App\Services\EmissionCalculatorService221;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateAktivitasRutin221 extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected EmissionCalculatorService221 $calculator
    ) {}

    /**
     * Tampilkan daftar kisi template aktivitas rutin (Read).
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $templates = ActivityTemplate221::with(['emissionFactor.category'])
            ->where('user_id', $userId)
            ->orderBy('name', 'asc')
            ->get();

        // Segarkan taksiran emisi jika faktor emisi nilainya berubah
        foreach ($templates as $t) {
            if ($t->emissionFactor && $t->emissionFactor->is_active) {
                $hasil = $this->calculator->calculate($t->emissionFactor, (float) $t->amount, auth()->user());
                if (abs((float) $t->estimated_emission - (float) $hasil['emisi_persis']) > 0.0001) {
                    $t->update(['estimated_emission' => $hasil['emisi_persis']]);
                }
            }
        }

        $kategoriList = Category::where('is_active', true)
            ->with(['emissionFactors' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $faktorEmisiList = EmissionFactor::where('is_active', true)
            ->with('category')
            ->get();

        return view('pencatatan-aktivitas.template-aktivitas-rutin-221.index', [
            'templates' => $templates,
            'kategoriList' => $kategoriList,
            'faktorEmisiList' => $faktorEmisiList,
            'bukaFormulir' => $request->get('buka') === 'buat',
        ]);
    }

    /**
     * Simpan template aktivitas rutin baru (Create).
     */
    public function store(SimpanTemplateAktivitasRutin221 $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Hitung estimasi emisi
        $hasil = $this->calculator->calculate(
            $validated['emission_factor_id'],
            (float) $validated['amount'],
            $user
        );

        ActivityTemplate221::create([
            'user_id' => $user->id,
            'emission_factor_id' => $validated['emission_factor_id'],
            'name' => $validated['name'],
            'amount' => round((float) $validated['amount'], 4),
            'estimated_emission' => $hasil['emisi_persis'],
        ]);

        return redirect()->route('template.index')
            ->with('sukses', "Template {$validated['name']} berhasil disimpan.");
    }

    /**
     * Perbarui template aktivitas rutin (Update).
     */
    public function update(SimpanTemplateAktivitasRutin221 $request, ActivityTemplate221 $template): RedirectResponse
    {
        $this->authorize('update', $template);

        $validated = $request->validated();
        $user = auth()->user();

        $hasil = $this->calculator->calculate(
            $validated['emission_factor_id'],
            (float) $validated['amount'],
            $user
        );

        $template->update([
            'emission_factor_id' => $validated['emission_factor_id'],
            'name' => $validated['name'],
            'amount' => round((float) $validated['amount'], 4),
            'estimated_emission' => $hasil['emisi_persis'],
        ]);

        return redirect()->route('template.index')
            ->with('sukses', "Template {$template->name} berhasil diperbarui.");
    }

    /**
     * Hapus template aktivitas rutin (Delete).
     */
    public function destroy(ActivityTemplate221 $template): RedirectResponse
    {
        $this->authorize('delete', $template);

        $nama = $template->name;
        $template->delete();

        return redirect()->route('template.index')
            ->with('sukses', "Template {$nama} berhasil dihapus.");
    }

    /**
     * Aksi cepat: Pakai Hari Ini.
     * Menyalin template menjadi satu baris aktivitas hari ini melalui Service yang sama.
     */
    public function pakai(ActivityTemplate221 $template): RedirectResponse
    {
        $this->authorize('view', $template);

        $factor = $template->emissionFactor;
        if (!$factor || !$factor->is_active) {
            return redirect()->route('template.index')
                ->with('galat', 'Faktor emisi untuk template ini telah diarsipkan, harap perbarui template.');
        }

        $user = auth()->user();

        // Hitung nilai emisi hari ini
        $hasil = $this->calculator->calculate($factor, (float) $template->amount, $user);

        Activitie221::create([
            'user_id' => $user->id,
            'emission_factor_id' => $template->emission_factor_id,
            'amount' => $template->amount,
            'emission' => $hasil['emisi_persis'],
            'activity_date' => Carbon::today()->toDateString(),
            'note' => "Dicatat dari template: {$template->name}",
        ]);

        return redirect()->route('template.index')
            ->with('sukses', "Tercatat dari template {$template->name}.");
    }
}
