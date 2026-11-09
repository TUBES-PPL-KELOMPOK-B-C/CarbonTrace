<?php

namespace App\Policies;

use App\Models\QuizQuestion221;
use App\Models\User;

class ManajemenBankPertanyaanKuisPolicy221
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('admin') || ($user->is_admin ?? false);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, QuizQuestion221 $quizQuestion221): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, QuizQuestion221 $quizQuestion221): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, QuizQuestion221 $quizQuestion221): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        // Pertanyaan yang sudah dijawab tidak boleh dihapus permanen
        return !$quizQuestion221->hasBeenAnswered();
    }
}
