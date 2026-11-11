<?php

namespace App\Events;

use App\Models\Target022;
use Illuminate\Foundation\Events\Dispatchable;

// PBI-10: dikirim setiap kali pemakaian sebuah target berubah, baik karena aktivitas dicatat,
// dikoreksi, atau dihapus, maupun karena targetnya diubah. Modul Edukasi (PBI-17) bisa
// menyimaknya untuk memunculkan rekomendasi aksi saat status "terlampaui", dan menghapusnya
// lagi bila status kembali di bawah batas.
class PemakaianTargetBerubah022
{
    use Dispatchable;

    /** @param  array{batas: float, terpakai: float, sisa: float, persen: float, status: string, sisa_hari: int}  $ringkasan */
    public function __construct(
        public readonly Target022 $target,
        public readonly array $ringkasan,
    ) {}
}
