<?php

Route::get('/site/structure/{id}', 'SiteController@structure');

Route::resource('/page', 'PageController');

Route::get('/definition', 'DefinitionController@show');