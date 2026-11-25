<?php

namespace App\Policies;

use App\Models\SavedReport022;
use App\Models\User;

// PBI-12: laporan hanya bisa dibuka, diubah, diunduh, dan dihapus oleh pemiliknya.
class LaporanJejakKarbonPolicy022
{
    public function view(User $user, SavedReport022 $laporan): bool
    {
        return $user->id === $laporan->user_id;
    }

    public function update(User $user, SavedReport022 $laporan): bool
    {
        return $user->id === $laporan->user_id;
    }

    public function delete(User $user, SavedReport022 $laporan): bool
    {
        return $user->id === $laporan->user_id;
    }
}
