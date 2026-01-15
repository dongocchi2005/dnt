<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
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
        $hotFile = public_path('hot');

        if (File::isFile($hotFile)) {
            $devServerUrl = trim((string) File::get($hotFile));

            if ($devServerUrl !== '') {
                $host = parse_url($devServerUrl, PHP_URL_HOST) ?: '127.0.0.1';
                $port = (int) (parse_url($devServerUrl, PHP_URL_PORT) ?: 5173);

                $socket = @fsockopen($host, $port, $errno, $errstr, 0.05);

                if (is_resource($socket)) {
                    fclose($socket);
                } else {
                    Vite::useHotFile(storage_path('framework/vite.hot'));
                }
            } else {
                Vite::useHotFile(storage_path('framework/vite.hot'));
            }
        }
    }
}
