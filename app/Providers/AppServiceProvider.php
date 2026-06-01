<?php

namespace App\Providers;

use App\Support\DeviceContext;
use App\Support\DevicePresence;
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
            if (!auth()->check()) {
                return;
            }

            $deviceId = DeviceContext::activeDeviceId();
            $latest = DevicePresence::latestForDevice($deviceId);

            $view->with('monitorDeviceId', $deviceId);
            $view->with('monitorDeviceOnline', DevicePresence::isOnline($latest, $deviceId));

            if (auth()->user()->is_admin) {
                $view->with('monitorDeviceIds', DeviceContext::availableDeviceIds());
            }
        });
    }
}
