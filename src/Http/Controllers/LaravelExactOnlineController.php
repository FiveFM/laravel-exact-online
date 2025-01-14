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
        \Log::info('Session Data:', session()->all());
        \Log::info('Request Cookies:', request()->cookies->all());
        \Log::info('easykas_session:', cookie('easykas_session'));

        if (session('oauth_state') === null) {
            \Log::error('OAuth state missing. Session might not be preserved.');
            abort(403, 'Session invalid or expired');
        }

        $decodedState = json_decode(decrypt(session('oauth_state')), true);

        if (!$decodedState || !isset($decodedState['user_id'])) {
            \Log::error('Invalid state: ' . $decodedState);
            abort(401, 'User not logged in');
        }

        $userId = $decodedState['user_id'];

        // Log the user in using the user ID
        Auth::loginUsingId($userId);

        // Clear the state from the session to prevent reuse
        session()->forget('oauth_state');

        // Save the authorization code
        $config = LaravelExactOnline::loadConfig();
        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        return redirect()->route('exact.form');
    }
}
