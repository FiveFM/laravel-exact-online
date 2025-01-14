<?php

namespace Fivefm\LaravelExactOnline;

use File;
use Illuminate\Support\Facades\Auth;

class LaravelExactOnline
{
    private $connection = [];

    /**
     * LaravelExactOnline constructor.
     */
    public function __construct()
    {
        \Log::info("CONSTRUCTING...");
        $this->connection = app()->make('Exact\Connection');
    }

    /**
     * Magically calls methods from Picqer Exact Online API
     *
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $arguments)
    {
        \Log::info("CALLING METHOD: " . $method);
        \Log::info("ARGUMENTS: " . json_encode($arguments));
        if (substr($method, 0, 10) == "connection") {

            $method = lcfirst(substr($method, 10));

            call_user_func([$this->connection, $method], implode(",", $arguments));

            return $this;
        } else {

            $classname = "\\Picqer\\Financials\\Exact\\" . $method;

            if (!class_exists($classname)) {
                throw new \Exception("Invalid type called");
            }

            return new $classname($this->connection);
        }
    }

    public static function loadConfig()
    {
        \Log::info("LOADING CONFIG...");
        if (config('laravel-exact-online.exact_multi_user')) {
            \Log::info("MULTI USER...");
            $exact = null;
            if (!Auth::check()) {
                \Log::info("NO AUTH...");
                $exact = new \App\Exact();
            } else {
                \Log::info("AUTH...");
                $exact = Auth::user()?->exact ?? new \App\Exact();
            }
            \Log::info("EXACT: " . json_encode($exact));
            return $exact;
        } else {
            \Log::info("SINGLE USER...");
            return (object)json_decode(
                File::get(
                    storage_path('exact.api.json')
                ),
                true
            );
        }
    }

    public static function storeConfig($config)
    {
        \Log::info("STORING CONFIG...");
        if (config('laravel-exact-online.exact_multi_user')) {
            \Log::info("MULTI USER...");
            \Log::info("AUTH: " . json_encode(Auth::user()));
            Auth::user()->exact()->save($config);
        } else {
            $file = storage_path('exact.api.json');
            File::put($file, json_encode($config));
        }
    }
}
