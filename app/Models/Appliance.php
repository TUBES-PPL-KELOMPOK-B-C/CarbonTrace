<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appliance extends Model
{
    use HasFactory;

    protected $table = 'appliances';

    protected $fillable = [
        'emission_factor_id',
        'name',
        'wattage',
    ];

    protected $casts = [
        'wattage' => 'decimal:2',
    ];

    public function emissionFactor(): BelongsTo
    {
        return $this->belongsTo(EmissionFactor::class, 'emission_factor_id');
    }
}
