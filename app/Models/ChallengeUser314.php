<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeUser314 extends Model
{
    use HasFactory;

    protected $table = 'challenge_user';

    protected $fillable = [
        'user_id',
        'challenge_id',
        'status',
        'progress',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'decimal:4',
            'joined_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User314::class, 'user_id');
    }
}
