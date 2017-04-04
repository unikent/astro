<?php

Route::get('/sites', 'SiteController@index');

Route::get('/sites/structure/{id}', 'SiteController@structure');

Route::resource('/pages', 'PageController');

Route::get('/definitions', 'DefinitionController@show');

Route::post('/upload', 'UploadController@store');

Route::resource('/media', 'MediaController');