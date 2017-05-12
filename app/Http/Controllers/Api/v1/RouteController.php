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
	 * @param  ResolveRequest $request
	 * @return Response
	 */
	public function resolve(ResolveRequest $request){
		$route = Route::findByPathOrFail($request->get('path'));

		$this->authorize('read', $route);
		return response($route->published_page->bake, 200);
	}

}
