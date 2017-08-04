<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Route\ResolveRequest;
use App\Models\Traits\ResolvesRoutes;

class PageController extends ApiController
{
    use ResolvesRoutes;

	/**
	 * GET /api/v1/route/resolve?path=...
	 *
	 * @param  ResolveRequest $request
	 * @return Response
	 */
	public function resolve(ResolveRequest $request){
		// Retrieve the path from the URL
		$path = $request->get('path');
        $host = $request->get('host');
		return $this->resolveRoute($host, $path);

	}

}
