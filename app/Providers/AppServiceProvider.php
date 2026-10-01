<?php

namespace App\Providers;

use Illuminate\Foundation\DevCommands;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');

        // `composer dev` already runs the web server and a queue worker (needed
        // for AI matching); add the scheduler so the daily auto-close of
        // returned items also runs locally.
        if ($this->app->runningInConsole()) {
            DevCommands::artisan('schedule:work', 'scheduler');
        }
    }
}
