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

// Preview draft pages in editor.
Route::get('/draft/{host}/{path?}', '\Astro\API\Http\Controllers\PageController@draft')
	->where('host', '([^/]+)')
	->where('path', '(.*?)/?')
	->middleware('auth');

// "Preview" published pages in editor.
Route::get('/published/{host}/{path?}', '\Astro\API\Http\Controllers\PageController@published')
	->where('host', '([^/]+)')
	->where('path', '(.*?)/?');

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
