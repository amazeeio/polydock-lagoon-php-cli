<?php

namespace App\Providers;

use FreedomtechHosting\FtLagoonPhp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implement if needed
    }

    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->bind(Client::class, function ($app, $parameters) {
            $config = array_merge(config('ftlagoonphp'), $parameters);

            return new Client($config);
        });
    }
}
