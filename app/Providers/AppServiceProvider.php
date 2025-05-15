<?php

namespace App\Providers;

use App\Services\FeedService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FeedService::class, function ($app) {
            return new FeedService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set Vietnamese as the default language
        App::setLocale('vi');

        // Use the custom bootstrap pagination theme
        Paginator::defaultView('vendor.pagination.custom-bootstrap');
        Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-5');
    }
}
