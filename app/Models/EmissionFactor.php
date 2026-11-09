<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmissionFactor extends Model
{
    use HasFactory;

    protected $table = 'emission_factors';

    protected $fillable = [
        'category_id',
        'name',
        'factor_value',
        'unit',
        'source_url',
        'is_active',
    ];

    protected $casts = [
        'factor_value' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function appliances(): HasMany
    {
        return $this->hasMany(Appliance::class, 'emission_factor_id');
    }

    public function portionUnits(): HasMany
    {
        return $this->hasMany(PortionUnit::class, 'emission_factor_id');
    }
}
