<?php

Route::group([ 'prefix' => 'v1', 'namespace' => 'v1' ], function(){
	Route::get('block/definitions', 'BlockController@definitions');
	Route::get('block/{block_definition}/definition', 'BlockController@definition');
	Route::get('block/{block_definition}/blocks', 'BlockController@blocks');

	Route::get('layout/definitions', 'LayoutController@definitions');
	Route::get('layout/{layout_definition}/definition', 'LayoutController@definition');

	// Media does not use resource routing as {media} gets pluralized to {medium}, resulting in dragons.
	Route::get('media', 'MediaController@index');
	Route::post('media', 'MediaController@store');
	Route::delete('media/{media}', 'MediaController@destroy');

	Route::resource('page', 'PageContentController', [ 'except' => [ 'index', 'create', 'edit' ]]);
	Route::post('page/{page}/publish', 'PageContentController@publish');
	Route::post('page/{page}/publish-tree', 'PageContentController@publishTree');
	Route::post('page/{page}/revert', 'PageContentController@revert');
	Route::delete('page/{page}/force', 'PageContentController@forceDestroy');

	Route::get('region/definitions', 'RegionController@definitions');
	Route::get('region/{region_definition}/definition', 'RegionController@definition');

	Route::get('route/resolve', 'PageController@resolve');

	Route::resource('site', 'SiteController', [ 'only' => [ 'index', 'show', 'store', 'update', 'destroy' ]]);
	Route::get('site/{site}/tree', 'SiteController@tree');

	// Route::resource('region', '');
	// Route::resource('block', '');
});
