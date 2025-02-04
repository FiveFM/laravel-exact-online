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
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Session\Session;

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
        $ses = new Session();
        Log::info($ses->get('user') ?? "User is er niet!");
        $user = $ses->get('user');
        Auth::shouldUse('web');
        Auth::loginUsingId($user);
        //        $id = Crypt::decryptString(request()->get('user'));
        $config = LaravelExactOnline::loadConfig();

        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        $connection = app()->make('Exact\Connection');
        session(['user' => $user]);
        return redirect()->route('exact.form');
        //        return redirect("easykas://return");
    }
}
