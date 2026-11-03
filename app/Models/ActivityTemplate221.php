<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityTemplate221 extends Model
{
    use HasFactory;

    /**
     * Nama tabel MySQL untuk template aktivitas berulang.
     */
    protected $table = 'activity_templates';

    protected $fillable = [
        'user_id',
        'emission_factor_id',
        'name',
        'amount',
        'estimated_emission',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'estimated_emission' => 'decimal:4',
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
