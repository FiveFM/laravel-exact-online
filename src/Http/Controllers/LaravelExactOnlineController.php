<?php

namespace Fivefm\LaravelExactOnline\Http\Controllers;

use App\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Fivefm\LaravelExactOnline\LaravelExactOnline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class LaravelExactOnlineController extends Controller
{
    /**
     * Connect Exact app
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function appConnect()
    {
        return view('laravelexactonline::connect');
    }

    /**
     * Authorize to Exact
     * Sends an oAuth request to the Exact App to get tokens
     */
    public function appAuthorize()
    {
        $connection = app()->make('Exact\Connection');
        return ["url" => $connection->getAuthUrl()];
    }

    /**
     * Exact Callback
     * Saves the authorisation and refresh tokens
     */
    public function appCallback()
    {
        //        $id = Crypt::decryptString(request()->get('user'));
        Auth::shouldUse('web');
        Auth::loginUsingId(session()->get('user'));

        $config = LaravelExactOnline::loadConfig();
        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        $connection = app()->make('Exact\Connection');
        session(['user' => request()->get('user')]);
        return redirect()->route('exact.form');
        //        return redirect("easykas://return");
    }
}
