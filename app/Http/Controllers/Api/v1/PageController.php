<?php
namespace App\Http\Controllers\Api\v1;

use DB;
use Exception;
use App\Models\Page;
use App\Models\Block;
use App\Models\Route;
use Illuminate\Http\Request;
use App\Http\Requests\Api\v1\Page\StoreRequest;
use App\Http\Requests\Api\v1\Page\UpdateRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PageController extends ApiController
{

	/**
	 * POST /api/v1/page
	 *
	 * @param  StoreRequest $request
	 * @param  Page $page
	 * @return Response
	 */
	public function store(StoreRequest $request){
		$page = new Page;
		$this->process($request, $page); // Handles authorization and persistance
		return response()->json([ 'data' => $page ], 201);
	}

	/**
	 * PUT /api/v1/page/{page}
	 * PATCH /api/v1/page/{page}
	 *
	 * @param UpdateRequest $request
	 * @param Page $page
	 * @return Response
	 */
	public function update(UpdateRequest $request, Page $page){
		$this->process($request, $page); // Handles authorization and persistance
		return response()->json([ 'data' => $page ], 200);
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
	 * Creates/Updates and persists the page and its associated blocks,
	 * using a transaction to ensure consistency.
	 *
	 * @param  Request $request
	 * @param  Page    $page
	 * @return void
	 */
	protected function process(Request $request, Page &$page){
		DB::beginTransaction();

		try {
			// Create/Update the Route
			// TODO: Validation needs to ensure that the route is within the appropriate hierarchy.
			// TODO: Validation needs to ensure that the route is not Canonical for another page.
			$route = Route::firstOrNew([
				'slug' => $request->input('route.slug'),
				'parent_id' => $request->input('route.parent_id', null),
			]);

			// Set the Page attributes and save the Page (we need an ID for the Route)
			$page->fill($request->all());
			$page->save();

			// Associate the Route with the Page
			$route->page_id = $page->getKey();

			// Now that we have a populated Route object, lets save the Route
			$route->save();

			// Now we have a Page in a state that we can authorize, so lets do that
			// Note: as we are within a Transaction, ALL changes will be rolled-back should authz fail
			$this->authorize($page->wasRecentlyCreated ? 'create' : 'update', $page);

			// Now we can make the Route canonical
			// Note: we don't do this pre-authz as permissions check requires knowledge of existing canonical Route
			$route->makeCanonical();

			// Populate the regions with Blocks
			if($request->has('regions')){
				foreach($request->input('regions') as $region => $blocks){
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
