<?php

namespace Fivefm\LaravelExactOnline\Http\Controllers;

use App\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Fivefm\LaravelExactOnline\LaravelExactOnline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;

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
        Log::info(request()->get('code'));
        //        $id = Crypt::decryptString(request()->get('user'));
        $config = LaravelExactOnline::loadConfig();

        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        $connection = app()->make('Exact\Connection');
        session(['user' => request()->get('user')]);
        return redirect()->route('exact.form');
        //        return redirect("easykas://return");
    }
}
