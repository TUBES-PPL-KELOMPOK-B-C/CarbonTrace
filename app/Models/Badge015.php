<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// PBI-13 Manajemen Lencana (KF-14).
class Badge015 extends Model
{
    use HasFactory;

    // Nama model berakhiran NIM, jadi tabelnya harus disebut sendiri.
    protected $table = 'badges';

    // Keempat jenis syarat yang dikenali mesin pemeriksa, beserta satuan yang dibaca pengguna.
    public const JENIS = [
        'poin' => 'poin',
        'runtutan' => 'hari berturut-turut',
        'aktivitas' => 'aktivitas tercatat',
        'emisi' => 'kg CO2e tercatat',
    ];

    protected $fillable = [
        'name',
        'description',
        'icon',
        'type',
        'required_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'required_value' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function pengguna(): BelongsToMany
    {
        return $this->belongsToMany(User314::class, 'badge_user', 'badge_id', 'user_id')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function scopeAktif(Builder $kueri): Builder
    {
        return $kueri->where('is_active', true);
    }

    // "1000 poin", "7 hari berturut-turut". Desimal bulat ditulis tanpa angka nol di belakang koma.
    public function syaratTerbaca(): string
    {
        return $this->angkaTerbaca($this->required_value).' '.(self::JENIS[$this->type] ?? $this->type);
    }

    // "840 dari 1000 poin" pada batang kemajuan lencana yang masih terkunci.
    public function kemajuanTerbaca(float $capaian): string
    {
        return $this->angkaTerbaca($capaian).' dari '.$this->syaratTerbaca();
    }

    private function angkaTerbaca(float|string $angka): string
    {
        $angka = (float) $angka;

        return $angka == (int) $angka
            ? number_format($angka, 0, ',', '.')
            : rtrim(rtrim(number_format($angka, 2, ',', '.'), '0'), ',');
    }
}
