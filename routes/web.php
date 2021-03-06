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

if(config('sso.enable_sso')) {
	$this->any('auth/sso', 'Auth\JWTController@ssoAuthenticate')->name('auth.jwt.sso.authenticate');
	$this->any('auth/jwt/reset', 'Auth\JWTController@resetSSOToken')->name('auth.jwt.sso.reset');
}
elseif(config('app.debug') && config('app.enable_jwt_dev_routes')) {
	$this->any('auth/jwt', 'Auth\JWTController@devAuthenticate')->name('auth.jwt.dev.authenticate');
	$this->any('auth/jwt/reset', 'Auth\JWTController@resetDevToken')->name('auth.jwt.dev.reset');
}

// SPA wrapper
Route::get('/{catchall?}', function($route = '') {
	return response()->view('inline', [
		'route'      => $route,
        'is_preview' => starts_with($route, 'preview/')
	]);
})
->where('catchall', '(.*)');
// ->middleware('auth');
