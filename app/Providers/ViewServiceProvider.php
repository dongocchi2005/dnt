<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            View::share('logo', null);

            return;
        }

        $logo = null;

        try {
            $logoClass = 'App\\Models\\Logo';

            if (class_exists($logoClass)) {
                $logo = $logoClass::query()->first();
            }
        } catch (\Throwable) {
            $logo = null;
        }

        View::share('logo', $logo);
    }
}
