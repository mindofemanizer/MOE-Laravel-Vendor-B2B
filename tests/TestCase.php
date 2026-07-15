<?php

namespace Moe\VendorB2B\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            \Moe\VendorB2B\VendorB2BServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('vendor-b2b.models.user', \Moe\VendorB2B\Tests\Stubs\User::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
            \Illuminate\Support\Facades\Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->timestamps();
            });
        }

        if (! \Illuminate\Support\Facades\Schema::hasTable('products')) {
            \Illuminate\Support\Facades\Schema::create('products', function ($table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
