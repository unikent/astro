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
use Intervention\Image\Facades\Image;

// Overwrite routes from plugin
Route::group(['prefix' => 'auth'], function() {
	Route::get('login', ['as' => 'auth.login', 'uses' => '\App\Http\Controllers\Auth\AuthController@getLogin']);
	Route::post('login', ['as' => 'auth.postlogin', 'uses' => '\App\Http\Controllers\Auth\AuthController@postLogin']);
	Route::get('logout', ['as' => 'auth.logout', 'uses' => '\App\Http\Controllers\Auth\AuthController@getLogout']);
	Route::get('loggedout', ['as' => 'auth.loggedout', 'uses' => '\App\Http\Controllers\Auth\AuthController@getLoggedout']);
});

Route::get('/image', function() {
	$img = Image::make(public_path() . '/bg.jpg');

	$type = 'blur';

	switch($type) {
		case 'blur':

			$img->resize(50, 50, function($constraint) {
				$constraint->aspectRatio();
			});

			$img->{$type}(3);
			break;
	}

	echo '<img src="' . $img->encode('data-url') . '" style="height: 100vh" />';
});

Route::get('/{catchall?}', function($route) {
	return response()->view('inline', ['route' => $route, 'user' => Auth::user()->name]);
})
->where('catchall', '(.*)')
->middleware('auth');


// rotate, crop, resize, resizeCanvas, orientate, fit
// http://image.intervention.io/use/filters, height, width