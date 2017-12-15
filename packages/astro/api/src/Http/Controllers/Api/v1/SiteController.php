<?php

namespace Astro\API\Http\Controllers\Api\v1;

use Astro\API\Models\APICommands\UpdateSite;
use Astro\API\Models\Definitions\SiteDefinition;
use Astro\API\Models\LocalAPIClient;
use Astro\API\Models\Page;
use Astro\API\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Astro\API\Transformers\Api\v1\SiteTransformer;
use Astro\API\Transformers\Api\v1\PageTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
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
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$this->authorize('index', Site::class);
		$api = new LocalAPIClient(Auth::user());
		$transformer = new SiteTransformer($request->get('version', Page::STATE_DRAFT));
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
		$this->authorize('create', Site::class);
		$api = new LocalAPIClient(Auth::user());
		$site = $api->createSite(
			$request->get('name'),
			$request->get('host'),
			$request->get('path'),
			$request->get('site_definition', []),
			$request->get('options')
		);
		if ($site instanceof Site) {
			return fractal($site, new SiteTransformer)->respond(201);
		} else {
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
		$this->authorize('assign', $site);
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
		// @TODO split API into different methods to handle different levels of permission
		// for eg updating options vs updating site path, domain, etc.
		if(array_first($request->all(), function($value, $field){
			return in_array($field, UpdateSite::UPDATABLE_PRIMITIVE_FIELDS);
		})){
			$this->authorize('update', $site);
		}elseif($request->has('options')){
			$this->authorize('updateOptions', $site);
		}
		$api = new LocalAPIClient(Auth::user());
		$site = $api->updateSite($site->id, $request->all());
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
	 * @param  Request $request
	 * @param  Site $site
	 * @return Response
	 */
	public function show(Request $request, Site $site)
	{
		$this->authorize('view', $site);
		return fractal($site, new SiteTransformer($request->get('version', Page::STATE_DRAFT)))
			->parseIncludes($request->get('include'))
			->respond();
	}

	/**
	 * GET /api/v1/sites/{site}/tree
	 *
	 * @param  Request $request
	 * @param  Site $site
	 * @return Response
	 */
	public function tree(Request $request, Site $site)
	{
		$this->authorize('view', $site);
		$data = $this->pagesToHierarchy(
			fractal(
				$site->pages($request->get('version', Page::STATE_DRAFT))
					->orderBy('lft')
					->with(['revision'])
					->get(),
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

	/**
	 * PUT/PATCH /api/v1/sites/{site}/???
	 * @param Request $request
	 * @param Page $page
	 * @return object
	 */
	public function move(Request $request, Site $site)
	{
		$this->authorize('movepages', $site);
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
		foreach ($nodes as $node) {
			$map[$node['id']] = $node;
			$map[$node['id']]['children'] = [];
			$map[$node['parent_id']]['children'][] =& $map[$node['id']]; // assign by reference is required on this line ONLY
		}
		return $map[null]['children'];
	}

	/**
	 * GET /sites/definitions
	 * Get a list of available site definitions.
	 */
	public function definitions()
	{
		$this->authorize('index', SiteDefinition::class);
		$path = sprintf('%s/%s/', Config::get('app.definitions_path'), SiteDefinition::$defDir);
		$sites = glob($path . '*/v*/definition.json');
		$path_length = strlen($path);
		foreach($sites as &$site){
			$site = preg_replace('/\/(v[0-9]+)\/definition\.json$/', '-$1', substr($site, $path_length));
		}
		$site_definitions = $this->getSiteDefinitions($sites);
		return response()->json([ 'data' => $site_definitions ]);
	}

		/**
		 * Get site definitions, indexed by {name}-v{version}
		 * @param array $site_ids - Array of site ids to retrieve definitions for [ {name}-v{version}, ...]
		 * @return array - Array of site definitions, indexed by site id
		 */
	public function getSiteDefinitions($site_ids)
	{
		$sites = [];
		foreach($site_ids as $site_id) {
			$sites[$site_id] = SiteDefinition::fromDefinitionFile(SiteDefinition::locateDefinition($site_id));
		}
		return $sites;
	}
}
