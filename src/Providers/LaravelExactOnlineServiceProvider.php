<?php

namespace Fivefm\LaravelExactOnline\Providers;

use Illuminate\Support\ServiceProvider;
use Picqer\Financials\Exact\Connection;
use Fivefm\LaravelExactOnline\LaravelExactOnline;

class LaravelExactOnlineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');

        $this->loadViewsFrom(__DIR__ . '/../views', 'laravelexactonline');

        $this->publishes([
            __DIR__ . '/../views' => base_path('resources/views/vendor/laravelexactonline'),
            __DIR__ . '/../exact.api.json' => storage_path('exact.api.json'),
            __DIR__ . '/../config/laravel-exact-online.php' => config_path('laravel-exact-online.php'),
        ]);
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->alias(LaravelExactOnline::class, 'laravel-exact-online');

        $this->app->singleton('Exact\Connection', static function () {
            $config = LaravelExactOnline::loadConfig();

            $connection = new Connection();
            $connection->setRedirectUrl(route('exact.callback'));
            $connection->setExactClientId(config('laravel-exact-online.exact_client_id'));
            $connection->setExactClientSecret(config('laravel-exact-online.exact_client_secret'));
            $connection->setBaseUrl('https://start.exactonline.' . config('laravel-exact-online.exact_country_code'));

            if (config('laravel-exact-online.exact_division') !== '') {
                $connection->setDivision(config('laravel-exact-online.exact_division'));
            }

            if (isset($config->exact_authorisationCode)) {
                $connection->setAuthorizationCode($config->exact_authorisationCode);
            }

            // Init connection items (just as when the token is refreshed)
            LaravelExactOnline::tokenRefreshCallback($connection);

            $connection->setTokenUpdateCallback([LaravelExactOnline::class, 'tokenUpdateCallback']);
            $connection->setRefreshAccessTokenCallback([LaravelExactOnline::class, 'tokenRefreshCallback']);
            $connection->setAcquireAccessTokenLockCallback([LaravelExactOnline::class, 'acquireLock']);
            $connection->setAcquireAccessTokenUnlockCallback([LaravelExactOnline::class, 'releaseLock']);

            $connection->connect();

            return $connection;
        });
    }
}
