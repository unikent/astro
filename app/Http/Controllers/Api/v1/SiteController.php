<?php
namespace App\Http\Controllers\Api\v1;

use App\Models\Site;
use Illuminate\Http\Request;
use App\Http\Transformers\Api\v1\SiteTransformer;
use App\Http\Transformers\Api\v1\RouteTransformer;

class SiteController extends ApiController
{

	/**
	 * GET /api/v1/site
	 *
	 * @param  Request    $request
	 * @return Response
	 */
	public function index(Request $request){
		$this->authorize('index', Site::class);

		$sites = Site::with('canonical')->get();
		return fractal($sites, new SiteTransformer)->respond();
	}

	/**
	 * GET /api/v1/site/{site}
	 * This endpoint supports 'include'.
	 *
	 * @param  Request    $request
	 * @param  Site $site
	 * @return Response
	 */
	public function show(Request $request, Site $site){
		$this->authorize('read', $site);
		return fractal($site, new SiteTransformer)->parseIncludes($request->get('include'))->respond();
	}

	/**
	 * GET /api/v1/site/{site}/tree
	 *
	 * @param  Request    $request
	 * @param  Site $site
	 * @return Response
	 */
	public function tree(Request $request, Site $site){
		$this->authorize('read', $site);

		$qb = $site->canonical->descendantsAndSelf();
		$routes = $qb->get()->toHierarchy();

		return fractal($routes, new RouteTransformer)->respond();
	}

}
