<?php

// Route::get('/sites', 'SiteController@index');

// Route::get('/sites/structure/{id}', 'SiteController@structure');

// Route::resource('/pages', 'PageController');

// Route::get('/definitions', 'DefinitionController@show');

// Route::post('/upload', 'UploadController@store');

// Route::resource('/media', 'MediaController');
//
//

Route::group([ 'prefix' => 'v1', 'namespace' => 'v1' ], function(){
	Route::get('block/definitions', 'BlockController@definitions');
	Route::get('block/{block_definition}/definition', 'BlockController@definition');
	Route::get('block/{block_definition}/blocks', 'BlockController@blocks');

	Route::get('layout/definitions', 'LayoutController@definitions');
	Route::get('layout/{layout_definition}/definition', 'LayoutController@definition');

	Route::resource('page', 'PageController', [ 'except' => [ 'index', 'create', 'edit' ]]);

	Route::get('region/definitions', 'RegionController@definitions');
	Route::get('region/{region_definition}/definition', 'RegionController@definition');

	// Route::resource('region', '');
	// Route::resource('block', '');
});
