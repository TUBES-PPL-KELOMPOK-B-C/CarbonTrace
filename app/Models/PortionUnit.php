<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortionUnit extends Model
{
    use HasFactory;

    protected $table = 'portion_units';

    protected $fillable = [
        'emission_factor_id',
        'name',
        'gram_per_portion',
    ];

    protected $casts = [
        'gram_per_portion' => 'decimal:2',
    ];

    public function emissionFactor(): BelongsTo
    {
        return $this->belongsTo(EmissionFactor::class, 'emission_factor_id');
    }
}
