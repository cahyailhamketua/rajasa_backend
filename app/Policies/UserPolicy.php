<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $authUser, User $targetUser): bool
    {
        // Super admin dapat mengupdate user mana pun.
        if ($authUser->role === 'super_admin') {
            return true;
        }

        // Admin hanya dapat mengupdate profile miliknya sendiri.
        return $authUser->id === $targetUser->id;
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        // Hanya super admin yang dapat menghapus user.
        return $authUser->role === 'super_admin';
    }
}