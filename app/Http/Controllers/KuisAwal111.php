<?php

namespace App\Http\Controllers;

use App\Models\QuizQuestion221;
use App\Notifications\KabarAdmin111;
use App\Services\KuisAwal111 as LayananKuis;
use App\Services\PerhitunganEmisi111;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

// KF-25 Pengisian Kuis Awal dan Garis Dasar (Alvin-111).
// Pengguna: satu soal per layar, jawaban tersimpan setiap pindah soal, boleh dilewati dan diulang.
// Administrator: ringkasan hasil kuis seluruh pengguna (soalnya sendiri dikelola di Bank Soal Kuis).
class KuisAwal111 extends Controller
{
    public function __construct(private readonly LayananKuis $kuis) {}

    public function mulai(Request $request): View|RedirectResponse
    {
        $soal = $this->kuis->soalAktif();
        if ($soal->isEmpty()) {
            return redirect()->route('dashboard.index')->with('info', 'Kuis awal belum tersedia. Administrator belum menyiapkan soal.');
        }

        $jawaban = $this->kuis->jawaban($request->user());

        // ?soal=N membuka soal ke-N (untuk tombol Kembali dan Ulangi); tanpa itu, soal pertama yang belum dijawab.
        $nomor = $request->integer('soal');
        if ($nomor < 1 || $nomor > $soal->count()) {
            $belum = $soal->search(fn ($s) => ! $jawaban->has($s->id));
            if ($belum === false) {
                return redirect()->route('kuis-awal.hasil');
            }
            $nomor = $belum + 1;
        }

        $sekarang = $soal[$nomor - 1];
        $terpilih = collect($sekarang->options)->search(fn ($o) => ($o['label'] ?? null) === $jawaban->get($sekarang->id)?->answer_label);

        return view('kuis-awal-111.soal', [
            'soal' => $sekarang,
            'nomor' => $nomor,
            'total' => $soal->count(),
            'terpilih' => $terpilih === false ? null : $terpilih,
        ]);
    }

    public function jawab(Request $request): RedirectResponse
    {
        $soal = $this->kuis->soalAktif();
        $sekarang = $soal->firstWhere('id', $request->integer('soal_id'));
        if (! $sekarang) {
            return redirect()->route('kuis-awal.mulai')->with('galat', 'Soal ini sudah tidak aktif. Lanjutkan ke soal berikutnya.');
        }

        $nomor = $soal->search(fn ($s) => $s->id === $sekarang->id) + 1;
        $request->validate(
            ['pilihan' => ['required', 'integer', 'min:0', 'max:'.(count($sekarang->options) - 1)]],
            ['pilihan.required' => 'Pilih salah satu jawaban dulu.', 'pilihan.*' => 'Pilihan jawaban tidak valid.'],
        );

        $this->kuis->simpan($request->user(), $sekarang, $request->integer('pilihan'));

        if ($nomor < $soal->count()) {
            return redirect()->route('kuis-awal.mulai', ['soal' => $nomor + 1]);
        }

        $garis = $this->kuis->garisDasar($request->user());
        $angka = PerhitunganEmisi111::angka($garis['nilai'], 2, true);

        KabarAdmin111::kirim(
            'Kuis Awal',
            "{$request->user()->name} menyelesaikan kuis awal. Garis dasarnya {$angka} kg CO2e per hari.",
            route('kuis-awal.ringkasan', absolute: false),
        );

        return redirect()->route('kuis-awal.hasil')
            ->with('sukses', "Kuis awal selesai. Garis dasar emisimu {$angka} kg CO2e per hari.");
    }

    public function lewati(Request $request): RedirectResponse
    {
        DB::table('users')->where('id', $request->user()->id)->update(['quiz_skipped_at' => now()]);

        return redirect()->route('dashboard.index')
            ->with('info', 'Kuis awal dilewati. Kamu bisa mengisinya kapan saja lewat menu Profil, Kuis Awal.');
    }

    public function hasil(Request $request): View|RedirectResponse
    {
        $soal = $this->kuis->soalAktif();
        $jawaban = $this->kuis->jawaban($request->user());

        if ($soal->isNotEmpty() && $jawaban->isEmpty()) {
            return redirect()->route('kuis-awal.mulai');
        }

        return view('kuis-awal-111.hasil', [
            'garis' => $this->kuis->garisDasar($request->user()),
            'rincian' => $soal->map(fn (QuizQuestion221 $s) => ['soal' => $s, 'jawaban' => $jawaban->get($s->id)]),
            'rataCatatan' => $this->rataCatatan30Hari($request->user()->id),
        ]);
    }

    // Halaman administrator: siapa saja yang sudah mengisi, rata-rata garis dasar, dan sebaran jawaban per soal.
    public function ringkasan(): View
    {
        $soal = $this->kuis->soalAktif();
        $idSoal = $soal->pluck('id')->all();

        // Akun administrator tidak dihitung, termasuk bila admin sempat mencoba kuisnya.
        $idAdmin = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', ['administrator', 'admin'])->select('model_has_roles.model_id');
        $pengguna = DB::table('users')->where('is_active', true)->whereNotIn('id', $idAdmin);

        $perPengguna = $this->kuis->tersedia() && $idSoal
            ? DB::table('onboarding_results as o')->join('users as u', 'u.id', '=', 'o.user_id')
                ->whereIn('o.quiz_question_id', $idSoal)
                ->whereNotIn('o.user_id', $idAdmin)
                ->groupBy('o.user_id', 'u.name', 'u.email')
                ->selectRaw('o.user_id, u.name, u.email, SUM(o.score) as garis_dasar, COUNT(*) as terjawab, MAX(o.updated_at) as terakhir')
                ->orderByDesc('terakhir')->get()
            : collect();

        $sebaran = $this->kuis->tersedia() && $idSoal
            ? DB::table('onboarding_results')->whereIn('quiz_question_id', $idSoal)
                ->groupBy('quiz_question_id', 'answer_label')->selectRaw('quiz_question_id, answer_label, COUNT(*) as jumlah')
                ->get()->groupBy('quiz_question_id')
            : collect();

        return view('kuis-awal-111.ringkasan', [
            'jumlahPengguna' => (clone $pengguna)->count(),
            'sudahMengisi' => $perPengguna->count(),
            'melewati' => Schema::hasColumn('users', 'quiz_skipped_at')
                ? (clone $pengguna)->whereNotNull('quiz_skipped_at')->whereNotIn('id', $perPengguna->pluck('user_id'))->count()
                : 0,
            'rataGarisDasar' => $perPengguna->isEmpty() ? null : round($perPengguna->avg('garis_dasar'), 2),
            'soal' => $soal,
            'sebaran' => $sebaran,
            'daftar' => $perPengguna->take(50),
        ]);
    }

    private function rataCatatan30Hari(int $penggunaId): ?float
    {
        if (! Schema::hasTable('activities')) {
            return null;
        }

        $baris = DB::table('activities')->where('user_id', $penggunaId)
            ->where('activity_date', '>=', now('Asia/Jakarta')->subDays(29)->toDateString())
            ->selectRaw('COUNT(DISTINCT activity_date) as hari, COALESCE(SUM(emission), 0) as total')->first();

        return $baris->hari ? round($baris->total / $baris->hari, 2) : null;
    }
}
