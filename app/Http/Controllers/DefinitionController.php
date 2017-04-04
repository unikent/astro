<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;

use Illuminate\Http\Request;

class DefinitionController extends ApiController
{
    public function show($type = null)
	{
		$path = base_path(config('app.blocks_path'));
		$json = [];

		$parent_dir = new \DirectoryIterator($path);

		foreach($parent_dir as $dir)
		{
			if($dir->isDir() && !$dir->isDot())
			{
				$def_file = $dir->getPathname() . '/definition.json';

				if(file_exists($def_file))
				{
					$json[$dir->getFilename()] = json_decode(file_get_contents($def_file) , true);
				}
			}
		}

		if(is_null($type))
		{
			return $this->success($json);
		}

		if(!array_key_exists($type, $json))
		{
			return response([
				'message' => sprintf('Config "%s" does not exist.', $type)
			], 404);
		}

		return $json[$type];
	}
}
