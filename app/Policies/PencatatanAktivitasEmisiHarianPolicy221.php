<?php

namespace App\Policies;

use App\Models\Activitie221;
use App\Models\User;

class PencatatanAktivitasEmisiHarianPolicy221
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Activitie221 $activitie221): bool
    {
        return $user->id === $activitie221->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Activitie221 $activitie221): bool
    {
        return $user->id === $activitie221->user_id;
    }

    public function delete(User $user, Activitie221 $activitie221): bool
    {
        return $user->id === $activitie221->user_id;
    }
}
