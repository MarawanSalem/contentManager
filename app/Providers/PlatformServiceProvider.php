<?php

namespace App\Providers;

use App\Services\Platforms\PlatformServiceFactory;
use App\Services\Platforms\PlatformServiceInterface;
use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PlatformServiceFactory::class, function ($app) {
            return new PlatformServiceFactory();
        });
    }
}
