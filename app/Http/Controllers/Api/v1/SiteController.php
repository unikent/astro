<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Site\StoreRequest;
use Auth;
use Gate;
use App\Models\Site;
use Illuminate\Http\Request;
use App\Http\Transformers\Api\v1\SiteTransformer;
use App\Http\Transformers\Api\v1\RouteTransformer;

class SiteController extends ApiController
{

	/**
	 * GET /api/v1/site
	 *
	 * Returns a list of sites accessible to the User. If the
	 * User had 'index' access, this will return all Sites.
	 *
	 * @param  Request    $request
	 * @return Response
	 */
	public function index(Request $request){
		$user = Auth::user();
		$sites = Site::with('routes');

		if(!Gate::allows('index', Site::class)){
			$pgs = $user->publishing_groups->pluck('id');
			$sites = $sites->whereIn('publishing_group_id', $pgs);
		}

		return fractal($sites->get(), new SiteTransformer)->respond();
	}

    /**
     * POST /api/v1/site
     *
     * Create a new site.
     * @return Response
     *
     */
	public function store(StoreRequest $request)
    {
        $site = new Site($request->all());
        $this->authorize( 'create', $site);
        $site->save();
        return fractal($site, new SiteTransformer)->respond(201);
    }

    /**
     * PUT/PATCH /api/v1/site/{site}
     *
     * Update an existing site.
     * @return \Response
     */
    public function update(Request $request, Site $site)
    {
        return null;
    }

    /**
     * DELETE /api/v1/site/{site}
     *
     * Delete an existing site.
     */
    public function destroy(Request $request, Site $site)
    {
        $this->authorize('delete', $site);
        return null;
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

		$qb = $site->activeRoute->descendantsAndSelf();
		$routes = $qb->get()->toHierarchy();

		return fractal($routes, new RouteTransformer)->respond();
	}

}
