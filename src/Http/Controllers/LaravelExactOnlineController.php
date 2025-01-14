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
            \Log::error('User not logged in');
            abort(401, 'User not logged in');
        }

        // Generate and store the state
        $state = encrypt(json_encode([
            'user_id' => $userId,
            'timestamp' => now()->timestamp,
        ]));
        session(['oauth_state' => $state]); // Store the state in the session for validation

        \Log::info(json_encode([
            'user_id' => $userId,
            'timestamp' => now()->timestamp,
        ]));
        
        // Append the state to the auth URL
        $authUrl = $connection->getAuthUrl() . '&state=' . urlencode($state);

        return ['url' => $authUrl];
    }


    public function appCallback()
    {
        $state = request('state');

        if (!$state || $state !== session('oauth_state')) {
            \Log::error('Invalid state: ' . $state);
            abort(401, 'User not logged in');
        }

        // Decrypt the state to get the original data
        $decodedState = json_decode(decrypt($state), true);

        if (!$decodedState || !isset($decodedState['user_id'])) {
            \Log::error('Invalid state: ' . $state);
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
