<?php

namespace App\Policies;

use App\Models\User314;
use App\Models\UserSetting314;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengaturanKonsumsiRumahTanggaPolicy314
{
    use HandlesAuthorization;

    public function view(User314 $user, ?UserSetting314 $setting = null): bool
    {
        if ($setting === null) {
            return true;
        }

        return $user->id === $setting->user_id;
    }

    public function update(User314 $user, ?UserSetting314 $setting = null): bool
    {
        if ($setting === null) {
            return true;
        }

        return $user->id === $setting->user_id;
    }

    public function delete(User314 $user, UserSetting314 $setting): bool
    {
        return $user->id === $setting->user_id;
    }
}
