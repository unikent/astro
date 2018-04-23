<?php

/*
|--------------------------------------------------------------------------
| Web Preview Routes
|--------------------------------------------------------------------------
|
| Routes for previewing draft or published version of web pages.
| Can be disabled by setting a DISABLE_PREVIEW_ROUTES in the env
|
*/
// Preview draft pages in editor.
Route::get('/draft/{host}/{path?}', 'PageController@draft')
	->where('host', '([^/]+)')
	->where('path', '(.*?)/?');

// "Preview" published pages in editor.
Route::get('/published/{host}/{path?}', 'PageController@published')
	->where('host', '([^/]+)')
	->where('path', '(.*?)/?');

