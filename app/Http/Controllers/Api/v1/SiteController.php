<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Site\StoreRequest;
use App\Models\LocalAPIClient;
use App\Models\Route;
use Auth;
use Gate;
use DB;
use App\Models\Site;
use App\Models\Page;
use Illuminate\Http\Request;
use App\Http\Transformers\Api\v1\SiteTransformer;
use App\Http\Transformers\Api\v1\RouteTransformer;
use Illuminate\Validation\ValidationException;

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
	public function store(Request $request)
    {
        $api = new LocalAPIClient();
        $site = $api->createSite(
            $request->get('publishing_group_id'),
            $request->get('name'),
            $request->get('host'),
            $request->get('path'),
            $request->get('layout_name'),
            $request->get('layout_version'),
            $request->get('options')
        );
        if($site instanceof Site) {
            return fractal($site, new SiteTransformer)->respond(201);
        }else{
            throw new ValidationException($site);
        }
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
