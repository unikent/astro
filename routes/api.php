<?php

Route::get('/site/structure/{id}', 'SiteController@structure');

Route::get('/page', 'PageController@index');
Route::get('/page/{page_id}', 'PageController@showApi');
Route::put('/page/{page_id}', 'PageController@update');

Route::get('/definition/{guid?}', 'DefinitionController@show');