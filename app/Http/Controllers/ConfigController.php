<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function show($guid = null)
	{
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
		}', true);

		if(is_null($guid)) {
			return $json;
		}

		if(!array_key_exists($guid, $json)) {
			return response([
				'message' => sprintf('Config "%s" does not exist.', $guid)
			], 404);
		}

		return $json[$guid];
	}
}
