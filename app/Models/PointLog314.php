<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointLog314 extends Model
{
    use HasFactory;

    protected $table = 'point_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'points',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User314::class, 'user_id');
    }
}
