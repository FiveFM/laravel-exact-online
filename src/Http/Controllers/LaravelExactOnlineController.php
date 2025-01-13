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
        // Retrieve the user ID from the session
        $userId = session('user_id');
        if (!$userId) {
            \Log::error('User session (already) expired. Please restart the authorization process. userId: ' . $userId);
            \Log::info('Session data: ' . json_encode(Session::all()));
            abort(401, 'User session expired. Please restart the authorization process.');
        }

        Auth::loginUsingId($userId);

        $config = LaravelExactOnline::loadConfig();
        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        return redirect()->route('exact.form');
    }
}
