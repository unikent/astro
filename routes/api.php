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
	Route::get('layout/definitions', 'LayoutController@definitions');
	Route::get('layout/{layout_definition}/definition', 'LayoutController@definition');
	Route::get('layout/{layout_definition}/regions', 'LayoutController@regions');

	Route::resource('layout', 'LayoutController');

	// Route::resource('region', '');
	// Route::resource('block', '');
});
