<?php

namespace App\Providers;

use App\Models\Logo;
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
        try {
            if (class_exists(Logo::class)) {
                View::share('logo', Logo::query()->first());
            } else {
                View::share('logo', null);
            }
        } catch (\Throwable $e) {
            View::share('logo', null);
        }
    }
}
