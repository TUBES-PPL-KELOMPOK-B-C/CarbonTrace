<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// PBI-13: satu baris per lencana yang sudah diraih seorang pengguna.
class BadgeUser015 extends Model
{
    // Nama model berakhiran NIM, jadi tabelnya harus disebut sendiri.
    protected $table = 'badge_user';

    // Tabel pivot tanpa kolom id tunggal; kuncinya gabungan user_id dan badge_id.
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User314::class, 'user_id');
    }

    public function lencana(): BelongsTo
    {
        return $this->belongsTo(Badge015::class, 'badge_id');
    }
}
