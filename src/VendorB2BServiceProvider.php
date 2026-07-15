<?php

namespace Moe\VendorB2B;

use Illuminate\Support\ServiceProvider;

class VendorB2BServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/vendor-b2b.php', 'vendor-b2b');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/vendor-b2b.php' => config_path('vendor-b2b.php'),
        ], 'vendor-b2b-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'vendor-b2b-migrations');
    }
}
