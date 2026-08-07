<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Guarantees route()/url() always generate https:// links in production — the QR
        // code on the PDF Event Pass encodes an absolute URL via route(), and a scheme
        // mismatch (e.g. APP_URL left on http://) would make it silently non-clickable.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
