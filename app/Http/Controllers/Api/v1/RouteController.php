<?php
namespace App\Http\Controllers\Api\v1;

use Gate;
use App\Models\Route;
use Illuminate\Http\Request;
use App\Http\Transformers\Api\v1\PageTransformer;
use App\Http\Requests\Api\v1\Route\ResolveRequest;
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

		if(Gate::allows('read', $route)){
			if($route->published_page){
				return response($route->published_page->bake);
			} else {
				return fractal($route->page, new PageTransformer)->parseIncludes([ 'canonical', 'blocks' ])->respond();
			}
		} else {
			return (new SymfonyResponse())->setStatusCode(404);
		}

	}

}
