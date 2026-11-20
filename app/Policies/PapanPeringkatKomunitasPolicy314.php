<?php

namespace App\Policies;

use App\Models\User314;

class PapanPeringkatKomunitasPolicy314
{
    /**
     * Menentukan apakah pengguna dapat melihat papan peringkat.
     * Hanya pengguna yang berstatus aktif yang diizinkan mengakses.
     */
    public function viewAny(User314 $user): bool
    {
        return (bool) $user->is_active;
    }
}
