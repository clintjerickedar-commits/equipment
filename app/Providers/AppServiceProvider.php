<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Build notification items for the top bar.
        View::composer('layouts.app', function ($view) {
            $view->with('notifications', app(NotificationService::class)->forUser(auth()->user()));
        });
    }
}
