<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DefinitionController extends Controller
{
    public function show($guid = null)
	{
		$path = base_path(env('BLOCKS_PATH'));
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

		if(is_null($guid))
		{
			return $json;
		}

		if(!array_key_exists($guid, $json))
		{
			return response([
				'message' => sprintf('Config "%s" does not exist.', $guid)
			], 404);
		}

		return $json[$guid];
	}
}
