<?php

namespace App\Policies;

use App\Models\Target022;
use App\Models\User;

// PBI-10: target hanya bisa diubah dan dibatalkan oleh pemiliknya.
class ManajemenTargetEmisiBulananPolicy022
{
    public function update(User $user, Target022 $target): bool
    {
        return $user->id === $target->user_id;
    }

    public function delete(User $user, Target022 $target): bool
    {
        return $user->id === $target->user_id;
    }
}
