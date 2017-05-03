<?php
namespace App\Http\Controllers\Api\v1;

use DB;
use Exception;
use App\Models\Media;
use Illuminate\Http\Request;
use App\Http\Requests\Api\v1\Media\PersistRequest;
use App\Http\Transformers\Api\v1\MediaTransformer;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;


class MediaController extends ApiController
{

	/**
	 * POST /api/v1/media
	 *
	 * @param  PersistRequest $request
	 * @param  Media $media
	 * @return Response
	 */
	public function store(PersistRequest $request){
		$hash = Media::hash($request->file('upload'));
		$media = Media::findByHash($hash);

		DB::beginTransaction();

		try {
			if(!$media){
				$media = new Media;
				$media->setFile($request->file('upload'));
				$media->save();
			}

			$media->sites()->sync(array_merge($media->sites->allRelatedIds(), $request->get('site_ids')));
			$media->practiceGroups()->sync(array_merge($media->practiceGroups->allRelatedIds(), $request->get('practice_group_ids')));

			DB::commit();
		} catch(Exception $e){
			DB::rollBack();
			throw $e;
		}

		return fractal($media, new MediaTransformer)->respond(201);
	}

	/**
	 * DELETE /api/v1/media/{media}
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return SymfonyResponse
	 */
	public function destroy(Request $request, Media $media){
		$sites = Site::whereIn('id', $request->get('site_ids'))->get();
		$pgs = PracticeGroups::whereIn('id', $request->get('practice_group_ids'))->get();

		foreach($sites + $pgs as $model){
			$this->authorize('delete', [ $media, $model ]);
		}

		$site_ids = array_diff($media->sites->allRelatedIds(), $request->get('site_ids'));
		$media->sites->sync($site_ids);

		$practice_group_ids = array_diff($media->practiceGroups->allRelatedIds(), $request->get('practice_group_ids'));
		$media->practice_groups->sync($practice_group_ids);

    	return (new SymfonyResponse())->setStatusCode(200);
	}

}
