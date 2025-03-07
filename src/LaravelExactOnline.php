<?php

namespace Fivefm\LaravelExactOnline;

use File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exact;

class LaravelExactOnline
{
    private $connection = [];

    /**
     * LaravelExactOnline constructor.
     */
    public function __construct()
    {
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
        if (config('laravel-exact-online.exact_multi_user')) {
            $exact = Auth::user()->exact == null ? new Exact() : Auth::user()->exact;
            Log::debug($exact);
            return $exact;
        } else {
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
        if (config('laravel-exact-online.exact_multi_user')) {
            Log::debug($config);
            Auth::user()->exact()->save($config);
        } else {
            $file = storage_path('exact.api.json');
            File::put($file, json_encode($config));
        }
    }
}
