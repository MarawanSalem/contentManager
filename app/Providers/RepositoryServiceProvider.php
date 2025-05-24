<?php

namespace App\Providers;

use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\PlatformRepositoryInterface;
use App\Repositories\PostRepository;
use App\Repositories\PlatformRepository;
use App\Repositories\RepositoryInterface;
use App\Services\ImageService;
use App\Services\PostService;
use App\Services\PlatformService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(PlatformRepositoryInterface::class, PlatformRepository::class);

        // Bind repositories
        $this->app->bind(RepositoryInterface::class, function ($app) {
            return new PostRepository($app->make('App\Models\Post'));
        });

        $this->app->bind(PostRepository::class, function ($app) {
            return new PostRepository($app->make('App\Models\Post'));
        });

        $this->app->bind(PlatformRepository::class, function ($app) {
            return new PlatformRepository($app->make('App\Models\Platform'));
        });

        // Bind services
        $this->app->bind(PostService::class, function ($app) {
            return new PostService(
                $app->make(PostRepository::class),
                $app->make(ImageService::class)
            );
        });

        $this->app->bind(PlatformService::class, function ($app) {
            return new PlatformService($app->make(PlatformRepository::class));
        });

        $this->app->singleton(ImageService::class, function ($app) {
            return new ImageService();
        });
    }
}
