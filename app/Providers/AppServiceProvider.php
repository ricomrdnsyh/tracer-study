<?php

namespace App\Providers;

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
        app('url')->resolveMissingNamedRoutesUsing(function ($name, $parameters, $absolute) {
            if ($name === 'login-admin') {
                return route('login', $parameters, $absolute);
            }
            if ($name === 'login-admin.post') {
                return route('login.post', $parameters, $absolute);
            }
            return null;
        });
    }
}
