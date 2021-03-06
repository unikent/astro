<?php

Route::group(['prefix' => 'v1', 'namespace' => 'v1'], function () {
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
	Route::post('pages/{page}/copy', 'PageController@copy');

	Route::get('regions/definitions', 'RegionController@definitions');
	Route::get('regions/{region_definition}/definition', 'RegionController@definition');

	Route::get('route/resolve', 'PageController@resolve');

	Route::resource('sites', 'SiteController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
	Route::get('sitedefinitions', 'SiteController@definitions');
	Route::get('sites/{site}/tree', 'SiteController@tree');
	Route::patch('sites/{site}/tree', 'SiteController@move');
	Route::delete('sites/{site}/media/{media}', 'SiteController@deleteMedia')
			->where([
				'site' => '[0-9]+',
				'media' => '[0-9]+'
			]);

	Route::get('users', 'UserController@index');
	Route::get('users/{username}', 'UserController@view')->where('username', '([a-z0-9_-]+)');
	Route::get('permissions', 'UserController@permissions');
	Route::get('roles', 'UserController@roles');
	Route::put('sites/{site}/users', 'SiteController@users');

	Route::post('command/swapsites', 'CommandController@swapsites');

});
