<?php
namespace App\Http\Controllers\Api\v1;

use Gate;
use App\Models\Route;
use App\Models\Redirect;
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
		// Retrieve the path from the URL
		$path = $request->get('path');

		// Attempt to resolve the Route
		$resolve = Route::findByPath($path);

		// If the Route is not found, attempt to find a Redirect
		if(!$resolve){
			$resolve = Redirect::findByPathOrFail($path);
		}

		if(Gate::allows('read', $resolve)){
			if($resolve->published_page){
				return response($resolve->published_page->bake);
			} else {
				return fractal($resolve->page, new PageTransformer)->parseIncludes([ 'blocks', 'active_route' ])->respond();
			}
		}

		return (new SymfonyResponse())->setStatusCode(404);
	}

}
