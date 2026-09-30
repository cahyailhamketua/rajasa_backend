<?php

namespace App\Policies;

use App\Models\ArticleCategory;
use App\Models\User;

class ArticleCategoryPolicy
{
    /**
     * Only super admin can create category.
     */
    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    /**
     * Only super admin can update category.
     */
    public function update(
        User $user,
        ArticleCategory $category
    ): bool {
        return $user->role === 'super_admin';
    }

    /**
     * Only super admin can delete category.
     */
    public function delete(
        User $user,
        ArticleCategory $category
    ): bool {
        return $user->role === 'super_admin';
    }
}