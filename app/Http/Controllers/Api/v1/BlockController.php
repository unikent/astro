<?php
namespace App\Http\Controllers\Api\v1;

use Auth;
use Config;
use Illuminate\Http\Request;
use App\Models\Definitions\Block as Definition;
use App\Http\Transformers\Api\v1\Definitions\BlockTransformer;

class BlockController extends ApiController
{

	/**
	 * GET /api/v1/region/definitions
	 * This endpoint supports 'include'.
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return Response
	 */
	public function definitions(Request $request){
		$this->authorize('index', Definition::class);

		$path = sprintf('%s/%s/', Config::get('app.definitions_path'), Definition::$defDir);
		$blocks = glob($path . '*', GLOB_ONLYDIR);

		foreach($blocks as &$block){
			$block = str_replace($path, '', $block);
		}

		return response()->json([ 'data' => $blocks ]);
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
		return fractal($definition, new BlockTransformer)->respond();
	}

}
