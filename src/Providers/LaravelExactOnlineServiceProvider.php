<?php

namespace Fivefm\LaravelExactOnline\Providers;

use Illuminate\Support\ServiceProvider;
use Fivefm\LaravelExactOnline\LaravelExactOnline;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LaravelExactOnlineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');
        $this->loadViewsFrom(__DIR__ . '/../views', 'laravelexactonline');

        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/vendor/laravelexactonline'),
            __DIR__ . '/../exact.api.json' => storage_path('exact.api.json'),
            __DIR__ . '/../config/laravel-exact-online.php' => config_path('laravel-exact-online.php'),
        ]);
    }

    public function register()
    {
        $this->app->alias(LaravelExactOnline::class, 'laravel-exact-online');

        $this->app->singleton('Exact\Connection', function () {
            $config = LaravelExactOnline::loadConfig();

            $connection = new \Picqer\Financials\Exact\Connection();

            $connection->setRedirectUrl(route('exact.callback'));
            $connection->setExactClientId(config('laravel-exact-online.exact_client_id'));
            $connection->setExactClientSecret(config('laravel-exact-online.exact_client_secret'));
            $connection->setBaseUrl('https://start.exactonline.' . config('laravel-exact-online.exact_country_code'), ['user' => Auth::user()->id]);

            if (isset($config->company_id)) {
                $connection->setDivision($config->company_id);
            }

            if (isset($config->authorisationCode)) {
                $connection->setAuthorizationCode($config->authorisationCode);
            }

            if (isset($config->accessToken)) {
                $connection->setAccessToken(unserialize($config->accessToken));
            }

            if (isset($config->refreshToken)) {
                $connection->setRefreshToken($config->refreshToken);
            }

            if (isset($config->tokenExpires)) {
                $connection->setTokenExpires($config->tokenExpires);
            }

            $connection->setTokenUpdateCallback('\App\Exact::tokenUpdateCallback');

            try {
                $connection->connect();
            } catch (\Exception $e) {
                throw new \Exception('Could not connect to Exact: ' . $e->getMessage());
            }

            return $connection;
        });
    }
}
