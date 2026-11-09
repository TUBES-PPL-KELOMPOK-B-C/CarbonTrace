<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class QuizQuestion221 extends Model
{
    use HasFactory;

    /**
     * Nama tabel MySQL untuk bank soal kuis emisi.
     */
    protected $table = 'quiz_questions';

    protected $fillable = [
        'question',
        'options',
        'emission_weight',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'emission_weight' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    /**
     * Periksa apakah pertanyaan ini pernah dijawab oleh pengguna (ada di onboarding_results).
     */
    public function hasBeenAnswered(): bool
    {
        if (DB::getSchemaBuilder()->hasTable('onboarding_results')) {
            return DB::table('onboarding_results')->where('quiz_question_id', $this->id)->exists();
        }
        return false;
    }

    public function answerCount(): int
    {
        if (DB::getSchemaBuilder()->hasTable('onboarding_results')) {
            return DB::table('onboarding_results')->where('quiz_question_id', $this->id)->count();
        }
        return 0;
    }
}
