<?php

namespace App\Rules;

use App\Models\Target022;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

// PBI-10: periode target tidak boleh beririsan dengan target lain milik pengguna yang sama.
// Dipakai saat membuat maupun mengubah target (target yang sedang diubah dikecualikan).
class PeriodeTidakTumpangTindih022 implements DataAwareRule, ValidationRule
{
    private array $data = [];

    public function __construct(private readonly int $penggunaId, private readonly ?int $kecualiId = null) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $atribut, mixed $nilai, Closure $gagal): void
    {
        $mulai = (string) $nilai;
        $selesai = (string) ($this->data['period_end'] ?? $mulai);

        if (self::bentrok($this->penggunaId, $mulai, $selesai, $this->kecualiId)) {
            $gagal(self::PESAN);
        }
    }

    public const PESAN = 'Kamu sudah punya target untuk periode ini. Ubah target yang ada atau pilih periode lain.';

    // Dua rentang beririsan bila yang satu mulai sebelum yang lain selesai, dan sebaliknya.
    // Dipakai juga oleh controller untuk memeriksa ulang di dalam transaksi saat menyimpan.
    public static function bentrok(int $penggunaId, string $mulai, string $selesai, ?int $kecualiId = null): bool
    {
        return Target022::where('user_id', $penggunaId)
            ->when($kecualiId, fn ($q) => $q->whereKeyNot($kecualiId))
            ->where('period_start', '<=', $selesai)
            ->where('period_end', '>=', $mulai)
            ->exists();
    }
}
