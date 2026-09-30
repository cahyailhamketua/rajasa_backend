<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Gallery;
use App\Models\User;
use App\Policies\ArticleCategoryPolicy;
use App\Policies\ArticlePolicy;
use App\Policies\GalleryPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Gallery::class, GalleryPolicy::class);
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(ArticleCategory::class, ArticleCategoryPolicy::class);
    }
}