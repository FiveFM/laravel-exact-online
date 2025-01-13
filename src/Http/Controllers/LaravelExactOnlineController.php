<?php

namespace Fivefm\LaravelExactOnline\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Fivefm\LaravelExactOnline\LaravelExactOnline;
use Illuminate\Support\Facades\Auth;

class LaravelExactOnlineController extends Controller
{
    /**
     * Connect Exact app
     *
     * @return Factory|View
     */
    public function appConnect()
    {
        return view('laravelexactonline::connect');
    }

    /**
     * Authorize to ExactOnline
     * Sends an oAuth request to the Exact App to get tokens
     */
    public function appAuthorize(): void
    {
        $connection = app()->make('Exact\Connection');
        $connection->redirectForAuthorization();
    }

    /**
     * Exact Callback
     * Saves the authorisation and refresh tokens
     *
     * @return Factory|View
     */
    public function appCallback()
    {
        Auth::shouldUse('web');
        Auth::loginUsingId(request()->get('user'));

        $config = LaravelExactOnline::loadConfig();
        dd([
            'user' => request()->get('user'),
            'code' => request()->get('code'),
            'config_before' => $config
        ]);

        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        $config_after = LaravelExactOnline::loadConfig();
        dd(['config_after' => $config_after]);

        $connection = app()->make('Exact\Connection');
        session(['user' => request()->get('user')]);
        return redirect()->route('exact.form');
    }
}
