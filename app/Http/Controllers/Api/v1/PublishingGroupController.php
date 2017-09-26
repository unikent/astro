<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Transformers\Api\v1\PublishingGroupTransformer;
use App\Models\LocalAPIClient;
use Auth;
use App\Models\PublishingGroup;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublishingGroupController extends ApiController
{

	/**
	 * GET /api/v1/publishinggroups
	 *
	 * Returns the list of available publishing groups.
	 *
	 * @param  Request    $request
	 * @return Response
	 */
	public function index(Request $request){
        $this->authorize('index', PublishingGroup::class);
        $transformer = new PublishingGroupTransformer();
        $transformer->setAvailableIncludes(['sites', 'users']);
        $groups = PublishingGroup::orderBy('name')->get();
        $incs = explode(',', $request->get('include'));
        if(in_array('sites', $incs)){
            $groups->load('sites');
        }
        if(in_array('users', $incs)){
            $groups->load('users');
        }
		return fractal($groups, $transformer)
            ->parseIncludes($request->get('include'))
            ->respond();
	}

}
