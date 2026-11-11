<?php

namespace App\Models;

use App\Policies\PengingatPencatatanHarianPolicy022;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// PBI-11: satu jadwal pengingat harian. Jam disimpan sebagai waktu Asia/Jakarta "HH:MM:00".
#[UsePolicy(PengingatPencatatanHarianPolicy022::class)]
class Reminder022 extends Model
{
    protected $table = 'reminders';

    protected $fillable = ['user_id', 'remind_time', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Masukan "20:00" disimpan "20:00:00" supaya pencocokan jam di penjadwal selalu seragam.
    protected function remindTime(): Attribute
    {
        return Attribute::make(set: fn (string $nilai) => substr($nilai, 0, 5).':00');
    }

    // "20:00" untuk tampilan dan isian formulir.
    public function jam(): string
    {
        return substr((string) $this->remind_time, 0, 5);
    }
}
