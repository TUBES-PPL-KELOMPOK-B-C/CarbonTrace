<?php

namespace App\Policies;

use App\Models\Reminder022;
use App\Models\User;

// PBI-11: pengingat hanya bisa diubah, dinyalakan/dimatikan, dan dihapus oleh pemiliknya.
class PengingatPencatatanHarianPolicy022
{
    public function update(User $user, Reminder022 $pengingat): bool
    {
        return $user->id === $pengingat->user_id;
    }

    public function delete(User $user, Reminder022 $pengingat): bool
    {
        return $user->id === $pengingat->user_id;
    }
}
