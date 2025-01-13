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
        $userId = Auth::id();

        if (!$userId) {
            abort(401, 'User not authenticated.');
        }

        $state = encrypt($userId);
        session(['oauth_state' => $state]); // Store the state in the session for validation
        $authUrl = $connection->getAuthUrl() . '&state=' . $state;

        return ['url' => $authUrl];
    }


    public function appCallback()
    {
        \Log::info('Session data at callback:', Session::all());

        $state = request('state');

        // Validate the state
        if (!$state || decrypt($state) !== session('oauth_state')) {
            \Log::error('Invalid or missing state parameter.');
            abort(401, 'Invalid state. Please retry the authorization process.');
        }

        // Decrypt the user ID from the state
        $userId = decrypt($state);
        if (!$userId) {
            \Log::error('Unable to decrypt user ID from state.');
            abort(401, 'Authorization failed. Please retry.');
        }

        // Log the user in using the user ID
        Auth::loginUsingId($userId);

        // Store the authorization code in the config
        $config = LaravelExactOnline::loadConfig();
        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        \Log::info('User successfully authorized.', ['user_id' => $userId]);

        return redirect()->route('exact.form');
    }
}
