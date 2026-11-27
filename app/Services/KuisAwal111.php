<?php

namespace App\Services;

use App\Models\QuizQuestion221;
use App\Models\User314;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// KF-25 Pengisian Kuis Awal dan Garis Dasar (Alvin-111).
// Soal diambil dari Bank Soal Kuis (PBI-06, tabel quiz_questions) yang dikelola administrator.
// Nilai satu jawaban = bobot pilihan x bobot emisi soal (pengali garis dasar), sama dengan
// pratinjau di formulir Bank Soal Kuis. Garis dasar = jumlah nilai jawaban untuk soal yang masih
// aktif, dibaca sebagai perkiraan kg CO2e per hari. Jawaban disimpan di onboarding_results,
// satu baris per pengguna per soal.
class KuisAwal111
{
    public function tersedia(): bool
    {
        return Schema::hasTable('quiz_questions') && Schema::hasTable('onboarding_results');
    }

    /** @return Collection<int, QuizQuestion221> */
    public function soalAktif(): Collection
    {
        return $this->tersedia()
            ? QuizQuestion221::where('is_active', true)->orderBy('id')->get()
                ->filter(fn (QuizQuestion221 $s) => count($s->options ?? []) >= 2)->values()
            : collect();
    }

    /** @return Collection<int, object> jawaban pengguna, dikunci dengan id soal */
    public function jawaban(User314 $pengguna): Collection
    {
        return $this->tersedia()
            ? DB::table('onboarding_results')->where('user_id', $pengguna->id)->get()->keyBy('quiz_question_id')
            : collect();
    }

    public function nilai(QuizQuestion221 $soal, int $pilihan): float
    {
        return round((float) ($soal->options[$pilihan]['weight'] ?? 0) * (float) $soal->emission_weight, 4);
    }

    public function simpan(User314 $pengguna, QuizQuestion221 $soal, int $pilihan): void
    {
        DB::table('onboarding_results')->updateOrInsert(
            ['user_id' => $pengguna->id, 'quiz_question_id' => $soal->id],
            ['answer_label' => $soal->options[$pilihan]['label'], 'score' => $this->nilai($soal, $pilihan), 'updated_at' => now(), 'created_at' => now()],
        );
    }

    /** @return array{nilai: float, terjawab: int, total: int} */
    public function garisDasar(User314 $pengguna): array
    {
        $soal = $this->soalAktif();
        $jawaban = $this->jawaban($pengguna)->only($soal->pluck('id')->all());

        return ['nilai' => round((float) $jawaban->sum('score'), 2), 'terjawab' => $jawaban->count(), 'total' => $soal->count()];
    }

    public function administrator(User314 $pengguna): bool
    {
        return method_exists($pengguna, 'hasAnyRole') && $pengguna->hasAnyRole(['administrator', 'admin']);
    }

    // Diarahkan ke kuis setelah masuk: pengguna (bukan admin) yang belum pernah menjawab dan
    // belum memilih melewati, selama bank soal punya soal aktif.
    public function perluDiisi(User314 $pengguna): bool
    {
        return ! $pengguna->quiz_skipped_at
            && ! $this->administrator($pengguna)
            && $this->tersedia()
            && ! DB::table('onboarding_results')->where('user_id', $pengguna->id)->exists()
            && $this->soalAktif()->isNotEmpty();
    }
}
