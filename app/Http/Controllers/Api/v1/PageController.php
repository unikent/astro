<?php
namespace App\Http\Controllers\Api\v1;

use DB;
use Exception;
use App\Models\Page;
use App\Models\Site;
use App\Models\Block;
use App\Models\Route;
use Illuminate\Http\Request;
use App\Models\PublishedPage;
use App\Exceptions\UnpublishedParentException;
use App\Http\Requests\Api\v1\Page\PersistRequest;
use App\Http\Transformers\Api\v1\PageTransformer;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;


class PageController extends ApiController
{

	/**
	 * GET /api/v1/page/{page}
	 * This endpoint supports 'include'.
	 *
	 * @param  Request $request
	 * @param  Page $page
	 * @return Response
	 */
	public function show(Request $request, Page $page){
		$this->authorize('read', $page);
		return fractal($page, new PageTransformer)->parseIncludes($request->get('include'))->respond();
	}

	/**
	 * POST /api/v1/page
	 *
	 * @param  StoreRequest $request
	 * @return Response
	 */
	public function store(PersistRequest $request){
		$page = new Page;
		$this->process($request, $page); // Handles authorization and persistance

		return fractal($page, new PageTransformer)->respond(201);
	}

	/**
	 * PUT /api/v1/page/{page}
	 * PATCH /api/v1/page/{page}
	 *
	 * @param UpdateRequest $request
	 * @param Page $page
	 * @return Response
	 */
	public function update(PersistRequest $request, Page $page){
		$this->process($request, $page); // Handles authorization and persistance
		return fractal($page, new PageTransformer)->respond();
	}

	/**
	 * POST /api/v1/page/{page}/publish
	 *
	 * @param  Request $request
	 * @param  Page $page
	 * @return Response
	 */
	public function publish(Request $request, Page $page){
		$this->authorize('publish', $page);

		try {
			$page->publish(new PageTransformer);
			return response($page->published->bake, 200);
		} catch(UnpublishedParentException $e){
			return response([ 'errors' => [ $e->getMessage() ] ], 406);
		}
	}

	/**
	 * POST /api/v1/page/{page}/publish-tree
	 *
	 * @param  Request $request
	 * @param  Page $page
	 * @return Response
	 */
	public function publishTree(Request $request, Page $page){
		$routes = $page->draftRoute->descendantsAndSelf()->get();

		DB::beginTransaction();

		try {
			foreach($routes as $route){
				$this->authorize('publish', $route->page);
				$route->page->publish(new PageTransformer);
			}

			DB::commit();
			return response($page->published->bake, 200);

		} catch(UnpublishedParentException $e){
			DB::rollBack();
			return response([ 'errors' => [ $e->getMessage() ] ], 406);

		} catch(Exception $e){
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
	public function revert(Request $request, Page $page){
		$this->authorize('revert', $page);

		$published = PublishedPage::findOrFail($request->get('published_page_id'));
		$page->revert($published);

		return fractal($page, new PageTransformer)->respond();
	}

	/**
	 * DELETE /api/v1/page/{page}
	 *
	 * Soft-deletes a page, leaving Routes intact.
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return SymfonyResponse
	 */
	public function destroy(Request $request, Page $page){
		$this->authorize('delete', $page);

		$page->delete();
    	return (new SymfonyResponse())->setStatusCode(200);
	}

	/**
	 * DELETE /api/v1/page/{page}/confirm
	 *
	 * Hard-deletes a page, allowing the database to cascade and delete Routes too.
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return SymfonyResponse
	 */
	public function forceDestroy(Request $request, Page $page){
		$this->authorize('forceDelete', $page);

		$page->forceDelete();
    	return (new SymfonyResponse())->setStatusCode(200);
	}

	/**
	 * Persists Page, Route and Site objects, also persists Block object
	 * associations for the given Page.
	 *
	 * @param  Request $request
	 * @param  Page    $page
	 * @return void
	 */
	protected function process(Request $request, Page &$page){
		DB::beginTransaction();

		try {
			// Set the Page attributes and save the Page (we need an ID for the Route)
			$page->fill($request->all());
			$page->save();

			$draftRoute = new Route([
				'slug' => $request->input('route.slug'),
				'page_id' => $page->getKey(),
				'parent_id' => $request->input('route.parent_id', null),
			]);

			// Runs on Update...
			if($page->activeRoute){
				$activeRoute = $page->activeRoute;

				// If the Route has been moved in the tree, we need to create a new Route
				if($request->has('route.parent_id') && $request->get('route.parent_id') !== $activeRoute->parent_id){
					$draftRoute->parent_id = $request->input('route.parent_id');

					$draftRoute->save();
					$draftRoute->cloneDescendants($activeRoute);

				// If the Route has not changed position, inherit the active position in the tree
				} else {
					$draftRoute->parent_id = $activeRoute->parent_id;
				}
			}

			// If $draftRoute has not already been saved, save it.
			if($draftRoute->isDirty()) $draftRoute->save();

			// If a Site ID is present, attempt to retrieve the existing Site model.
			if($request->has('site_id')){
				$site = Site::findOrFail($request->get('site_id'));
			}

			// If site attributes are present, update the Site (or create a new one)
			// Then ensure that the Route is associated with the given Site.
			if($request->has('site')){
				$site = isset($site) ? $site : new Site;
				$site->fill($request->get('site'));
				$site->save();

				$this->authorize($site->wasRecentlyCreated ? 'create' : 'update', $site);

				// Note: makeSite takes effect immediately, and affects all published and unpublished Routes
				$draftRoute->makeSite($site);
			}

			// Now we have a Page in a state that we can authorize, so lets do that
			// Note: as we are within a Transaction, ALL changes will be rolled-back should authz fail
			$this->authorize($page->wasRecentlyCreated ? 'create' : 'update', $page);

			// Populate the regions with Blocks
			if($request->has('blocks')){
				foreach($request->input('blocks') as $region => $blocks){
					// Remove any existing Blocks in the region (to avoid re-ordering existing)
					$page->clearRegion($region);

					// Re/create all the blocks
					if(!empty($blocks)){
						foreach($blocks as $delta => $data){
							$block = new Block;

							$block->page_id = $page->getKey();

							$block->order = $delta;
							$block->region_name = $region;

							$block->fill($data);
							$block->save();
						}
					}
				}
			}

			DB::commit();
		} catch(Exception $e){
			DB::rollBack();
			throw $e;
		}
	}

}
