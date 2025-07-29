<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        Vite::prefetch(concurrency: 3);
        
        // Forzar HTTPS en producción
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
        
        // Configurar la URL base para las rutas
        $appUrl = config('app.url');
        if ($appUrl) {
            URL::forceRootUrl($appUrl);
            $this->app['request']->server->set('SCRIPT_NAME', parse_url($appUrl, PHP_URL_PATH) . '/index.php');
        }
    }
}