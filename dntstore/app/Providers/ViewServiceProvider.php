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
        if ($this->app->runningInConsole() || $this->isDisabledByEnv()) {
            View::share('logo', null);
            return;
        }

        View::composer('*', function ($view): void {
            static $resolved = false;
            static $logo = null;

            if (! $resolved) {
                $resolved = true;

                if (class_exists(Logo::class)) {
                    try {
                        $logo = Logo::query()->first();
                    } catch (\Throwable) {
                        $logo = null;
                    }
                }
            }

            $view->with('logo', $logo);
        });
    }

    private function isDisabledByEnv(): bool
    {
        $raw = getenv('DISABLE_VIEW_PROVIDER');
        if ($raw === false || $raw === '') {
            return false;
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }
}
