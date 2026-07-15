<?php
declare(strict_types=1);

namespace Moe\VendorB2B;

use Illuminate\Support\ServiceProvider;

class VendorB2BServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/vendor-b2b.php', 'vendor-b2b');
    }

    /**
     * Bootstrap the application services.
     */
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
