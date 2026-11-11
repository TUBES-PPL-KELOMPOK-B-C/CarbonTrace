<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting314 extends Model
{
    use HasFactory;

    protected $table = 'user_settings';

    protected $fillable = [
        'user_id',
        'household_size',
        'power_watt',
        'vehicle_ownership',
    ];

    protected $casts = [
        'household_size' => 'integer',
        'power_watt' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User314::class, 'user_id');
    }
}
