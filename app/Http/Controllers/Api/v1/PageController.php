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

		$page->publish(new PageTransformer);
		return response($page->published->bake, 200);
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
			// Create/Update the Route
			$route = Route::firstOrNew([
				'slug' => $request->input('route.slug'),
				'parent_id' => $request->input('route.parent_id', null),
			]);

			// Set the Page attributes and save the Page (we need an ID for the Route)
			$page->fill($request->all());
			$page->save();

			// Associate the Route with the Page
			$route->page_id = $page->getKey();

			// Now that we have a Route object, lets save it...
			$route->save();

			// ...and ensure that there are no other inactive Routes for this page
			$page->routes()->where($route->getKeyName(), '!=', $route->getKey())->active(false)->delete();

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
				$route->makeSite($site);
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
