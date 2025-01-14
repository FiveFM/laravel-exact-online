<?php

namespace Fivefm\LaravelExactOnline\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
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
        // Save the authorization code
        $config = LaravelExactOnline::loadConfig();
        $cookieTest = request()->cookie('easykas_session');

        \Log::info("COOKIE TEST: " . $cookieTest);
        \Log::info("REQUEST: " . json_encode(request()->all()));
        \Log::info("SESSION: " . json_encode(session()->all()));
        \Log::info("ALL COOKIES: " . json_encode(cookies()->all()));

        if ($cookieTest) {
            Auth::loginUsingId(decrypt($cookieTest));
        }

        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        return redirect()->route('exact.form');
    }
}
