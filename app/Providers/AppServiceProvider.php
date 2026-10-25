<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($publicPath = config('app.public_path')) {
            $this->app->usePublicPath($publicPath);
            config(['filesystems.disks.public.root' => $publicPath.'/storage']);
        }
        require_once app_path('Support/helpers.php');
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
