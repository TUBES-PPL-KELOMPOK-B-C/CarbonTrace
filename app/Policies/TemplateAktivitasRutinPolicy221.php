<?php

namespace App\Policies;

use App\Models\ActivityTemplate221;
use App\Models\User;

class TemplateAktivitasRutinPolicy221
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ActivityTemplate221 $activityTemplate221): bool
    {
        return $user->id === $activityTemplate221->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ActivityTemplate221 $activityTemplate221): bool
    {
        return $user->id === $activityTemplate221->user_id;
    }

    public function delete(User $user, ActivityTemplate221 $activityTemplate221): bool
    {
        return $user->id === $activityTemplate221->user_id;
    }
}
