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
        \Log::info(session()->all());

        // Decrypt the state to get the original data
        if (session('oauth_state') === null) {
            \Log::error('No state found');
            abort(403, 'User not found in session');
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
