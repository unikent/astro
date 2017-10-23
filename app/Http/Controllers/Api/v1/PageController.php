<?php

namespace App\Http\Controllers\Api\v1;


use App\Http\Transformers\Api\v1\PageTransformer;
use App\Models\APICommands\AddPage;
use App\Models\LocalAPIClient;
use Auth;
use DB;
use Exception;
use App\Models\Page;
use Illuminate\Http\Request;
use App\Models\Revision;
use App\Exceptions\UnpublishedParentException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use App\Http\Requests\Api\v1\Route\ResolveRequest;
use App\Models\Traits\ResolvesRoutes;

/**
 * Handles requests for pages via the api
 * *** NOTE ***
 * Where $page route parameters for controller methods are auto-resolved by the RouteProvider based on an id,
 * these will be restricted to pages in DRAFT version.
 * @package App\Http\Controllers\Api\v1
 */
class PageController extends ApiController
{
	use ResolvesRoutes;

	/**
	 * GET /api/v1/route/resolve?path=...
	 *
	 * @param  ResolveRequest $request
	 * @return Response
	 */
	public function resolve(ResolveRequest $request)
	{
		// Retrieve the path from the URL
		$path = $request->get('path');
		$host = $request->get('host');
		$include = $request->get('include');
		$version = $request->get('version', Page::STATE_DRAFT);
		return $this->resolveRoute($host, $path, $version, $include);

	}

	/**
	 * GET /api/v1/page/{page}
	 * This endpoint supports 'include'.
	 *
	 * @param  Request $request
	 * @param  Page $page
	 * @return Response
	 */
	public function show(Request $request, Page $page)
	{
		$this->authorize('read', $page);
		return fractal($page, new PageTransformer(true))->parseIncludes($request->get('include'))->respond();
	}

	/**
	 * POST /api/v1/page
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$parent = Page::findOrFail($request->get('parent_id'));
		$this->authorize('create', [Page::class, $parent->site_id]);
		$api = new LocalAPIClient(Auth::user());
		$page = $api->execute(AddPage::class, $request->all());

		return fractal($page, new PageTransformer(true))->respond(201);
	}

	/**
	 * PUT /api/v1/page/{page}
	 * PATCH /api/v1/page/{page}
	 * Update page options (title, settings, etc)
	 * @param Request $request
	 * @param Page $page
	 * @return Response
	 */
	public function update(Request $request, Page $page)
	{
		$this->authorize('update', $page);
		$api = new LocalAPIClient(Auth::user());
		$page = $api->updatePage(
			$page->id,
			$request->input('title'),
			$request->input('settings', null)
		);
		return fractal($page, new PageTransformer(true))->respond();
	}

	/**
	 * PUT /api/v1/page/{page}/slug
	 * Update page slug
	 * @param Request $request
	 * @param Page $page
	 * @return Response
	 */
	public function changeSlug(Request $request, Page $page)
	{
		$this->authorize('update', $page);
		$api = new LocalAPIClient(Auth::user());
		$page = $api->renamePage($page->id, $request->input('slug'));
		return fractal($page, new PageTransformer(true))->respond();
	}

	/**
	 * POST /api/v1/page/{page}/content
	 * Update page content (blocks)
	 * @param Request $request
	 * @param Page $page
	 * @return Response
	 */
	public function updateContent(Request $request, Page $page)
	{
		$this->authorize('update', $page);
		$api = new LocalAPIClient(Auth::user());
		$page = $api->updatePageContent($page->id, $request->input('blocks'));
		return fractal($page, new PageTransformer(true))->respond();
	}

	/**
	 * POST /api/v1/page/{page}/publish
	 *
	 * @param  Request $request
	 * @param  Page $page
	 * @return Response
	 */
	public function publish(Request $request, Page $page)
	{
		$this->authorize('publish', $page);
		$api = new LocalAPIClient(Auth::user());
		$published = $api->publishPage($page->id);
		return fractal($published, new PageTransformer(true))->respond();
	}

	/**
	 * POST /api/v1/page/{page}/publish-tree
	 *
	 * @param  Request $request
	 * @param  Page $page
	 * @return Response
	 */
	public function publishTree(Request $request, Page $page)
	{
		$this->authorize('publish', $page);
		$routes = $page->draftRoute->descendantsAndSelf()->get();

		DB::beginTransaction();

		try {
			foreach ($routes as $route) {
				$this->authorize('publish', $route->page);
				$route->page->publish(new PageTransformer);
			}

			DB::commit();
			return response($page->revision->blocks, 200);

		} catch (UnpublishedParentException $e) {
			DB::rollBack();
			return response(['errors' => [$e->getMessage()]], 406);

		} catch (Exception $e) {
			DB::rollBack();
			throw $e;
		}
	}

	/**
	 * POST /api/v1/page/{page}/revert
	 *
	 * @param  Request $request
	 * @param  Page $page
	 * @return Response
	 */
	public function revert(Request $request, Page $page)
	{
		$this->authorize('revert', $page);

		$revision = Revision::findOrFail($request->get('published_page_id'));
		$page->revert($revision);

		return fractal($page, new PageTransformer)->respond();
	}

	/**
	 * DELETE /api/v1/page/{page}
	 *
	 * Deletes a page, creates DeletedPages.
	 *
	 * @param  Request $request
	 * @param  Page $page
	 * @return SymfonyResponse
	 */
	public function destroy(Request $request, Page $page)
	{
		$this->authorize('delete', $page);
		$api = new LocalAPIClient(Auth::user());
		$api->deletePage($page->id);
		return (new SymfonyResponse())->setStatusCode(200);
	}

}
