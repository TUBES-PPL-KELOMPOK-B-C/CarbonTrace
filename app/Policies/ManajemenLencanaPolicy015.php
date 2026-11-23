<?php

namespace App\Policies;

use App\Models\Badge015;
use App\Models\User314;

// PBI-13: lencana hanya boleh dikelola administrator. Galeri lencana terbuka untuk semua
// pengguna yang sudah masuk, jadi tidak diatur di sini.
class ManajemenLencanaPolicy015
{
    public function viewAny(User314 $user): bool
    {
        return $this->administrator($user);
    }

    public function create(User314 $user): bool
    {
        return $this->administrator($user);
    }

    public function update(User314 $user, Badge015 $lencana): bool
    {
        return $this->administrator($user);
    }

    public function delete(User314 $user, Badge015 $lencana): bool
    {
        return $this->administrator($user);
    }

    // Pemeriksaan peran mengikuti PastikanAdministrator314: peran spatie dulu, lalu kolom
    // role_id, supaya hasilnya sama walau salah satu jalur belum terisi.
    private function administrator(User314 $user): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->hasRole('administrator')) {
            return true;
        }

        return (int) $user->role_id === 1;
    }
}
