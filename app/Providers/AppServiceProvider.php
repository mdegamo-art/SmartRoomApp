<?php

namespace App\Providers;

use App\Support\DeviceContext;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.app', function ($view) {
            if (!auth()->check() || !auth()->user()->is_admin) {
                return;
            }

            $view->with('monitorDeviceIds', DeviceContext::availableDeviceIds());
            $view->with('monitorDeviceId', DeviceContext::activeDeviceId());
        });
    }
}
