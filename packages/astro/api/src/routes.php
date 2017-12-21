<?php
Route::prefix('api/v1')
	->namespace('Astro\API\Http\Controllers\Api\v1')
	->middleware('api')
	->group(
		function () {
			Route::get('blocks/definitions', 'BlockController@definitions');
			Route::get('blocks/{block_definition}/definition', 'BlockController@definition');
			Route::get('blocks/{block_definition}/blocks', 'BlockController@blocks');

			Route::get('layouts/definitions', 'LayoutController@definitions');
			Route::get('layouts/{layout_definition}/definition', 'LayoutController@definition');

			// Media does not use resource routing as {media} gets pluralized to {medium}, resulting in dragons.
			Route::get('media', 'MediaController@index');
			Route::post('media', 'MediaController@store');
			Route::delete('media/{media}', 'MediaController@destroy');

			Route::resource('pages', 'PageController', ['except' => ['index', 'create', 'edit']]);
			Route::put('pages/{page}/content', 'PageController@updateContent');
			Route::put('pages/{page}/slug', 'PageController@changeSlug');
			Route::post('pages/{page}/publish', 'PageController@publish');
			Route::post('pages/{page}/publish-tree', 'PageController@publishTree');
			Route::post('pages/{page}/unpublish', 'PageController@unpublish');
			Route::post('pages/{page}/revert', 'PageController@revert');
			Route::delete('pages/{page}/force', 'PageController@forceDestroy');

			Route::get('regions/definitions', 'RegionController@definitions');
			Route::get('regions/{region_definition}/definition', 'RegionController@definition');

			Route::get('routes/resolve', 'PageController@resolve');

			Route::resource('sites', 'SiteController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
			Route::get('sitedefinitions', 'SiteController@definitions');
			Route::get('sites/{site}/tree', 'SiteController@tree');
			Route::patch('sites/{site}/tree', 'SiteController@move');

			Route::get('users', 'UserController@index');
			Route::get('users/{username}', 'UserController@view')->where('username', '([a-z0-9_-]+)');
			Route::get('permissions', 'UserController@permissions');
			Route::get('roles', 'UserController@roles');
			Route::put('sites/{site}/users', 'SiteController@users');

		}
	);

// Preview draft pages in editor.
Route::get('/draft/{host}/{path?}', '\Astro\API\Http\Controllers\PageController@draft')
	->where('host', '([^/]+)')
	->where('path', '(.*?)/?')
	->middleware('web', 'auth');

// "Preview" published pages in editor.
Route::get('/published/{host}/{path?}', '\Astro\API\Http\Controllers\PageController@published')
	->where('host', '([^/]+)')
	->middleware('web')
	->where('path', '(.*?)/?');
