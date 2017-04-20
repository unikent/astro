<?php
namespace App\Http\Controllers\Api\v1;

use Auth;
use Config;
use Illuminate\Http\Request;
use App\Models\Definitions\Region as Definition;

class RegionController extends ApiController
{

	/**
	 * GET /api/v1/region/definitions
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return Response
	 */
	public function definitions(Request $request){
		$this->authorize('index', Definition::class);

		$path = sprintf('%s/%s/', Config::get('app.definitions_path'), Definition::$defDir);
		$regions = glob($path . '*', GLOB_ONLYDIR);

		foreach($regions as &$region){
			$region = str_replace($path, '', $region);
		}

		return response()->json([ 'data' => $regions ]);
	}

	/**
	 * GET /api/v1/region/{region_definition}/definition
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return Response
	 */
	public function definition(Request $request, Definition $definition){
		$this->authorize('read', $definition);
		return response()->json([ 'data' => $definition ]);
	}

	/**
	 * GET /api/v1/region/{region_definition}/blocks
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return Response
	 */
	public function blocks(Request $request, Definition $definition){
		$this->authorize('read', $definition);
		return response()->json([ 'data' => $definition->getBlockDefinitions() ]);
	}

}
