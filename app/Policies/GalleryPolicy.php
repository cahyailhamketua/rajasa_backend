<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

class GalleryPolicy
{
    /**
     * Determine whether the user can create a gallery.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'super_admin',
        ]);
    }

    /**
     * Determine whether the user can update the gallery.
     */
    public function update(User $user, Gallery $gallery): bool
    {
        return $user->role === 'super_admin'
            || $gallery->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the gallery.
     */
    public function delete(User $user, Gallery $gallery): bool
    {
        return $user->role === 'super_admin'
            || $gallery->user_id === $user->id;
    }
}