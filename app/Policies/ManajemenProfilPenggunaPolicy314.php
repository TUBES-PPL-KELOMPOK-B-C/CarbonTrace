<?php

namespace App\Policies;

use App\Models\User314;
use Illuminate\Auth\Access\HandlesAuthorization;

class ManajemenProfilPenggunaPolicy314
{
    use HandlesAuthorization;

    public function view(User314 $user, User314 $targetUser): bool
    {
        return $user->id === $targetUser->id;
    }

    public function update(User314 $user, User314 $targetUser): bool
    {
        return $user->id === $targetUser->id;
    }

    public function delete(User314 $user, User314 $targetUser): bool
    {
        return $user->id === $targetUser->id;
    }
}
