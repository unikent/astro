<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;

// Overwrite routes from plugin
Route::group(['prefix' => 'auth'], function() {
	Route::get('login', ['as' => 'auth.login', 'uses' => '\App\Http\Controllers\Auth\AuthController@getLogin']);
	Route::post('login', ['as' => 'auth.postlogin', 'uses' => '\App\Http\Controllers\Auth\AuthController@postLogin']);
	Route::get('logout', ['as' => 'auth.logout', 'uses' => '\App\Http\Controllers\Auth\AuthController@getLogout']);
	Route::get('loggedout', ['as' => 'auth.loggedout', 'uses' => '\App\Http\Controllers\Auth\AuthController@getLoggedout']);
});

Route::get('/{catchall?}', function($route = '') {
	$user = Auth::user();
	// TODO: grab user info from endpoint, rather than inline js
	return response()->view('inline', [
		'route'      => $route,
		'is_preview' => starts_with($route, 'preview/'),
		'user'       => $user->name,
		'api_token'  => $user->api_token
	]);
})
->where('catchall', '(.*)')
->middleware('auth');
