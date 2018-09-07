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

// Authentication routes
$this->get('auth/login', 'Auth\AuthController@login')->name('auth.login');
$this->post('auth/login', 'Auth\AuthController@loginLocal');
$this->get('auth/sso/respond', 'Auth\AuthController@loginSSO')->name('auth.sso.respond');
$this->post('auth/logout', 'Auth\AuthController@logout')->name('auth.logout');

if(config('app.debug') && config('app.enable_jwt_dev_routes')) {
	$this->any('auth/jwt', 'Auth\JWTController@devAuthenticate')->name('auth.jwt.dev.authenticate');
	$this->get('auth/jwt/reset', 'Auth\JWTController@resetDevToken')->name('auth.jwt.dev.reset');
}

// SPA wrapper
Route::get('/{catchall?}', function($route = '') {
	$user = Auth::user();
	// TODO: grab user info from endpoint, rather than inline js
	return response()->view('inline', [
		'route'      => $route,
		'is_preview' => starts_with($route, 'preview/'),
		'user'       => $user->name,
		'username'   => $user->username,
		'api_token'  => $user->api_token
	]);
})
->where('catchall', '(.*)')
->middleware('auth');
