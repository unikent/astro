<?php
namespace Astro\API\Http\Controllers\Api\v1;

use Astro\API\Models\Definitions\Layout;
use Auth;
use Config;
use Illuminate\Http\Request;
use Astro\API\Models\Definitions\Layout as Definition;
use Astro\API\Transformers\Api\v1\Definitions\LayoutTransformer;

class LayoutController extends ApiController
{

	/**
	 * GET /api/v1/layout/definitions
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return Response
	 */
	public function definitions(Request $request){
		$this->authorize('index', Definition::class);

		$path = sprintf('%s/%s/', Config::get('app.definitions_path'), Definition::$defDir);
		$layouts = glob($path . '*/v*/definition.json');
		$path_length = strlen($path);
		foreach($layouts as &$layout){
			$layout = preg_replace('/\/(v[0-9]+)\/definition\.json$/', '-$1', substr($layout, $path_length));
		}
		$layouts = $this->getLayoutDefinitions($layouts);
		return response()->json([ 'data' => $layouts ]);
	}

	/**
	 * Get layout definitions, indexed by layout_id
	 * @param array $layout_ids - Array of layout ids to retrieve layouts for [ {name}-v{version}, ...]
	 * @return array - Array of layout definitions, indexed by layout id
	 */
	public function getLayoutDefinitions($layout_ids)
	{
		$layouts = [];
		foreach($layout_ids as $layout_id) {
			$layouts[$layout_id] = Layout::fromDefinitionFile(Layout::locateDefinition($layout_id));
		}
		return $layouts;
	}

	/**
	 * GET /api/v1/layout/{layout_definition}/definition
	 * This endpoint supports 'include'.
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return Response
	 */
	public function definition(Request $request, Definition $definition){
		$this->authorize('read', $definition);
		return fractal($definition, new LayoutTransformer)->parseIncludes($request->get('include'))->respond();
	}

}
