<?php
namespace App\Http\Controllers\Api\v1;

use Auth;
use App\Models\Page;
use Illuminate\Http\Request;
use App\Http\Transformers\Api\v1\Definitions\PageTransformer;

class RegionController extends ApiController
{

	/**
	 * GET /api/v1/site
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return Response
	 */
	public function index(Request $request){
		$this->authorize('index', Page::class);

		$sites = Page::sites()->get();
		return fractal($sites, new PageTransformer)->respond();
	}

}
