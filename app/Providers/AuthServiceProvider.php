<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Feed;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\FeedPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Article::class => ArticlePolicy::class,
        Category::class => CategoryPolicy::class,
        Feed::class => FeedPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
