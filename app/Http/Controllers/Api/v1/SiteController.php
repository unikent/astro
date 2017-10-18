<?php
namespace App\Http\Controllers\Api\v1;

use App\Models\LocalAPIClient;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Transformers\Api\v1\SiteTransformer;
use App\Http\Transformers\Api\v1\PageTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class SiteController extends ApiController
{

	/**
	 * GET /api/v1/sites
	 *
	 * Returns a list of sites accessible to the User. If the
	 * User had 'index' access, this will return all Sites.
	 *
	 * @param  Request    $request
	 * @return Response
	 */
	public function index(Request $request)
	{
	    $api = new LocalAPIClient(Auth::user());
	    $transformer = new SiteTransformer();
	    $transformer->setAvailableIncludes(['publishing_group','homepage','users']);
		return fractal($api->getSites(), $transformer)->parseIncludes($request->get('include'))->respond();
	}

    /**
     * POST /api/v1/sites
     *
     * Create a new site.
     * @return Response
     *
     */
	public function store(Request $request)
    {
        $api = new LocalAPIClient(Auth::user());
        $site = $api->createSite(
            $request->get('publishing_group_id'),
            $request->get('name'),
            $request->get('host'),
            $request->get('path'),
            $request->get('homepage_layout',[]),
            $request->get('options')
        );
        if($site instanceof Site) {
            return fractal($site, new SiteTransformer)->respond(201);
        }else{
            throw new ValidationException($site);
        }
    }

    /**
     * Assign (or remove) users' roles on this site.
     * PUT/PATCH /api/v1/sites/{site}/users
     * @param Request $request Input in form username => ..., role => ...
     * @param Site $site
     */
    public function users(Request $request, Site $site)
    {
        $api = new LocalAPIClient(Auth::user());
        $site = $api->updateSiteUserRole($site->id, $request->get('username'), $request->get('role'));
        return fractal($site, new SiteTransformer())->parseIncludes('users')->respond(200);
    }

    /**
     * PUT/PATCH /api/v1/sites/{site}
     *
     * Update an existing site.
     * @return \Response
     */
    public function update(Request $request, Site $site)
    {
        $api = new LocalAPIClient(Auth::user());
        $site = $api->updateSite( $site->id, $request->all());
		return fractal($site, new SiteTransformer())->respond(200);
    }

    /**
     * DELETE /api/v1/sites/{site}
     *
     * Delete an existing site.
     */
    public function destroy(Request $request, Site $site)
    {
        $this->authorize('delete', $site);
        return null;
    }

	/**
	 * GET /api/v1/sites/{site}
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
	 * GET /api/v1/sites/{site}/tree
	 *
	 * @param  Request    $request
	 * @param  Site $site
	 * @return Response
	 */
	public function tree(Request $request, Site $site){
		$this->authorize('read', $site);
        $site->load([
            'pages',
            'pages.revision',
        ]);
        $data = $this->pagesToHierarchy(
            fractal(
                $site->pages()->orderBy('lft')->with(['revision'])->get(),
                new PageTransformer()
            )
                ->parseIncludes($request->get('include'))
                ->toArray()
        );
        $data = [
           'data' => count($data) ? $data[0] : null
        ];
		return new JsonResponse($data);
	}

	public function move(Request $request)
    {
        $api = new LocalAPIClient(Auth::user());
        $result = $api->movePage(
            $request->get('page_id'),
            $request->get('parent_id'),
            $request->get('next_id')
        );
        return $result;
    }

    /**
     * Magickery
     * Turn a "flat" array of hierarchical data into a hierarchy.
     * @param array $nodes Array of nodes in depth-first order, each of which MUST have a parent id key.
     * @return hierarchical array where each node has its children keyed by 'children'
     */
	public function pagesToHierarchy($nodes)
    {
        $nodes = $nodes['data'];
        $map = [null => ['children' => []]];
        foreach($nodes as $node){
            $map[$node['id']] = $node;
            $map[$node['id']]['children'] = [];
            $map[$node['parent_id']]['children'][] =& $map[$node['id']]; // assign by reference is required on this line ONLY
        }
        return $map[null]['children'];
    }

}
