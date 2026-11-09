<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activitie221 extends Model
{
    use HasFactory;

    /**
     * Nama tabel MySQL yang dipakai bersama modul lain.
     */
    protected $table = 'activities';

    protected $fillable = [
        'user_id',
        'emission_factor_id',
        'amount',
        'emission',
        'activity_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'emission' => 'decimal:4',
        'activity_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function emissionFactor(): BelongsTo
    {
        return $this->belongsTo(EmissionFactor::class, 'emission_factor_id');
    }
}
