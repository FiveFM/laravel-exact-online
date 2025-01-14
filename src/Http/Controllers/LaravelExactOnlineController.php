<?php

namespace Fivefm\LaravelExactOnline\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Fivefm\LaravelExactOnline\LaravelExactOnline;

class LaravelExactOnlineController extends Controller
{
    public function appConnect()
    {
        return view('laravelexactonline::connect');
    }

    public function appAuthorize()
    {
        $connection = app()->make('Exact\Connection');
        return ['url' => $connection->getAuthUrl()];
    }


    public function appCallback()
    {
        \Log::info("CALLBACK...");
        // Save the authorization code
        $config = LaravelExactOnline::loadConfig();
        \Log::info("CONFIG: " . json_encode($config));
        \Log::info("REQUEST: " . json_encode(request()->all()));
        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        return redirect()->route('exact.form');
    }
}
