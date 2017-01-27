<?php

use App\Models\Page;
use Illuminate\Http\Request;
use App\Http\Controllers\PageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function(Request $request) {
	return $request->user();
})->middleware('auth:api');

Route::get('/page', function() {
	if(!isset($_GET['path']))
	{
		return Response::json(['error' => 'no path supplied']);
	}

	$page = Page::findByPath($_GET['path']);
	return Response::json($page);
});


Route::get('/page/{page_id}', function($page_id) {
	$page = Page::find($page_id)->getBlockStructure();
	return Response::json($page);
});

Route::get('/site/structure/{id}', function($id) {
	if($page = Page::find($id))
	{
		$pages = $page->descendantsAndSelf();
	}
	else
	{
		$pages = [];
	}

	return Response::json($pages);
});

Route::put('/page/{page_id}', 'PageController@update');

Route::get('/config', function(Request $request) {
	$json = json_decode('{
		"97a2e1b5-4804-46dc-9857-4235bf76a058": {
			"name": "Feature Panel",
			"guid": "97a2e1b5-4804-46dc-9857-4235bf76a058",
			"type": "feature-panel",
			"fields": [{
				"name": "image",
				"label": "Background Image",
				"type": "text"
			}, {
				"name": "block_heading",
				"label": "Block Heading",
				"type": "text",
				"required": true
			}, {
				"name": "block_description",
				"label": "Block Description",
				"type": "text",
				"default": 0
			}, {
				"name": "block_link",
				"label": "Block Link",
				"type": "text",
				"default": "standard"
			}, {
				"name": "image_alignment",
				"label": "Image Alignment",
				"type": "text",
				"options": {
					"top": "card-img-top",
					"bottom": "card-img-bottom",
					"center": "card-img"
				},
				"default": "card-img-top"
			}]
		},
		"9689fb47-834e-4d34-a8e3-06e4ea1b25bf": {
			"name": "Content Area",
			"guid": "9689fb47-834e-4d34-a8e3-06e4ea1b25bf",
			"type": "content",
			"fields": [{
				"name": "content",
				"type": "rich",
				"required": ""
			}],
			"view": "path/to/view"
		}
	}');

	return Response::json($json);
});