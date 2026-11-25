<?php

namespace App\Models;

use App\Policies\LaporanJejakKarbonPolicy022;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// PBI-12: satu laporan tersimpan. Hanya menyimpan judul, rentang, dan lingkup kategorinya; angkanya
// selalu dihitung ulang dari activities. Lingkup disimpan sebagai id dipisah koma, kosong = semua.
#[UsePolicy(LaporanJejakKarbonPolicy022::class)]
class SavedReport022 extends Model
{
    protected $table = 'saved_reports';

    protected $fillable = ['user_id', 'title', 'period_start', 'period_end', 'category_scope'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return int[] */
    public function kategoriIds(): array
    {
        return array_map('intval', array_filter(explode(',', (string) $this->category_scope)));
    }
}
