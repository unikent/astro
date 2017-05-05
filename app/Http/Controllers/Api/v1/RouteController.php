<?php
namespace App\Http\Controllers\Api\v1;

use App\Models\Route;
use Illuminate\Http\Request;
use App\Http\Requests\Api\v1\Route\ResolveRequest;
use App\Http\Transformers\Api\v1\RouteTransformer;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RouteController extends ApiController
{

	/**
	 * GET /api/v1/route/resolve?path=...
	 *
	 * This endpoint supports 'include'.
	 *
	 * @param  ResolveRequest $request
	 * @return Response
	 */
	public function resolve(ResolveRequest $request){
		$route = Route::findByPathOrFail($request->get('path'));

		$this->authorize('read', $route);

		return fractal($route, new RouteTransformer)->parseIncludes($request->get('include'))->respond();
	}

}
