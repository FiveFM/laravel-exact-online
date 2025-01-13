<?php

Route::group(['prefix' => 'exact', 'middleware' => config('laravel-exact-online.exact_multi_user') ? ['web', 'auth'] : ['web']], static function () {
    Route::get('connect', ['as' => 'exact.connect', 'uses' => 'Fivefm\LaravelExactOnline\Http\Controllers\LaravelExactOnlineController@appConnect']);
    Route::post('authorize', ['as' => 'exact.authorize', 'uses' => 'Fivefm\LaravelExactOnline\Http\Controllers\LaravelExactOnlineController@appAuthorize']);
    Route::get('oauth', ['as' => 'exact.callback', 'uses' => 'Fivefm\LaravelExactOnline\Http\Controllers\LaravelExactOnlineController@appCallback']);
});
