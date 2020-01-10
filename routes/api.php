<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')
    ->get('/profile', function (Request $request) {
        return response()->json($request->user());
    });


Route::delete('oauth/token', 'Auth\TokenController@delete')->middleware('auth:api');

Route::post('users', 'UserController@create');

Route::get('activate', 'UserController@activate');
