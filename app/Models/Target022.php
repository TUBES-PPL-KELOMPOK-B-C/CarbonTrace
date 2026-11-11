<?php

namespace App\Models;

use App\Policies\ManajemenTargetEmisiBulananPolicy022;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// PBI-10: batas emisi satu pengguna untuk satu periode. Tanggal disimpan sebagai teks Y-m-d
// (sesuai skema README), sehingga perbandingan antartanggal cukup memakai perbandingan teks.
#[UsePolicy(ManajemenTargetEmisiBulananPolicy022::class)]
class Target022 extends Model
{
    protected $table = 'targets';

    protected $fillable = ['user_id', 'period_start', 'period_end', 'emission_limit'];

    protected $casts = [
        'emission_limit' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
