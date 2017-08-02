<?php

Route::group([ 'prefix' => 'v1', 'namespace' => 'v1' ], function(){
	Route::get('blocks/definitions', 'BlockController@definitions');
	Route::get('blocks/{block_definition}/definition', 'BlockController@definition');
	Route::get('blocks/{block_definition}/blocks', 'BlockController@blocks');

	Route::get('layouts/definitions', 'LayoutController@definitions');
	Route::get('layouts/{layout_definition}/definition', 'LayoutController@definition');

	// Media does not use resource routing as {media} gets pluralized to {medium}, resulting in dragons.
	Route::get('media', 'MediaController@index');
	Route::post('media', 'MediaController@store');
	Route::delete('media/{media}', 'MediaController@destroy');

	Route::resource('pages', 'PageContentController', [ 'except' => [ 'index', 'create', 'edit' ]]);
	Route::post('pages/{page}/publish', 'PageContentController@publish');
	Route::post('pages/{page}/publish-tree', 'PageContentController@publishTree');
	Route::post('pages/{page}/revert', 'PageContentController@revert');
	Route::delete('pages/{page}/force', 'PageContentController@forceDestroy');

	Route::get('regions/definitions', 'RegionController@definitions');
	Route::get('regions/{region_definition}/definition', 'RegionController@definition');

	Route::get('routes/resolve', 'PageController@resolve');

	Route::resource('sites', 'SiteController', [ 'only' => [ 'index', 'show', 'store', 'update', 'destroy' ]]);
	Route::get('sites/{site}/tree', 'SiteController@tree');
});
