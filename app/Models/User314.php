<?php

namespace App\Models;

use Database\Factories\User314Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User314 extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'users';

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'photo',
        'campus',
        'study_program',
        'points',
        'streak_count',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory()
    {
        return User314Factory::new();
    }

    // Relasi ke tabel roles
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Relasi ke pengaturan rumah tangga (PBI-02)
    public function setting()
    {
        return $this->hasOne(UserSetting314::class, 'user_id');
    }

    public function userSetting()
    {
        return $this->hasOne(UserSetting314::class, 'user_id');
    }

    // Relasi ke riwayat poin (PBI-20)
    public function pointLogs()
    {
        return $this->hasMany(PointLog314::class, 'user_id');
    }

    // Relasi ke keikutsertaan tantangan (PBI-20)
    public function challengeUsers()
    {
        return $this->hasMany(ChallengeUser314::class, 'user_id');
    }
}
