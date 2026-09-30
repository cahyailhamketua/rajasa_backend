<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Determine whether the user can create an article.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'super_admin',
        ]);
    }

    /**
     * Determine whether the user can update the article.
     */
    public function update(
        User $user,
        Article $article
    ): bool {
        return $user->role === 'super_admin'
            || $article->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the article.
     */
    public function delete(
        User $user,
        Article $article
    ): bool {
        return $user->role === 'super_admin'
            || $article->user_id === $user->id;
    }
}