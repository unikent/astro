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
use Illuminate\Support\Facades\Config;
use Astro\Renderer\Base\SingleDefinitionsFolderLocator;
use Astro\Renderer\Engines\TwigEngine;
use Astro\Renderer\AstroRenderer;
use App\Models\LocalAPIClient;
use Illuminate\Http\Request;

$this->get('auth/login', 'Auth\AuthController@login')->name('auth.login');
$this->post('auth/login', 'Auth\AuthController@loginLocal');
$this->get('auth/sso/respond', 'Auth\AuthController@loginSSO')->name('auth.sso.respond');
$this->post('auth/logout', 'Auth\AuthController@logout')->name('auth.logout');

Route::get('/draft', function(Request $request) {

    // Template, definitions, etc locator
    $locator = new SingleDefinitionsFolderLocator(
        Config::get('app.definitions_path') ,
        'Astro\Renderer\Base\Block',
        'Astro\Renderer\Base\Layout'
    );
    $app_url = Config::get('app.url') . '/draft';
    $api = new LocalAPIClient();
    $engine = new TwigEngine(Config::get('app.definitions_path'));

    // controller
    $astro = new AstroRenderer();

    return $astro->renderRoute($request->input('host'), $request->input('path'), $api, $engine, $locator);
})
->where('route', '(.*?)/?')
->middleware('auth');

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
