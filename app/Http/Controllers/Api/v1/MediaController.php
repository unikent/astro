<?php
namespace App\Http\Controllers\Api\v1;

use DB;
use Exception;
use App\Models\Site;
use App\Models\Media;
use Illuminate\Http\Request;
use App\Models\PublishingGroup;
use Illuminate\Support\Collection;
use App\Http\Requests\Api\v1\Media\DeleteRequest;
use App\Http\Requests\Api\v1\Media\PersistRequest;
use App\Http\Transformers\Api\v1\MediaTransformer;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;


class MediaController extends ApiController
{

	/**
	 * POST /api/v1/media
	 *
	 * Handles the creation of new Media objects. If the file has previously been uploaded,
	 * we associate the existing Media with the specified Site/PublishingGroup. From the users'
	 * perspective, the upload completed.
	 *
	 * @param  PersistRequest $request
	 * @param  Media $media
	 * @return Response
	 */
	public function store(PersistRequest $request){
		$file = $request->file('upload');
		$hash = Media::hash($file);

		$media = Media::findByHash($hash);
		$media = $media ?: new Media([ 'file' => $file ]);

		$this->authorizeAll($request, 'create', $media); // Ensures that the user can sync with Sites / Practice Groups

		DB::beginTransaction();

		try {
			$media->save();

			$site_ids = array_merge($media->sites()->allRelatedIds()->toArray(), $request->get('site_ids'));
			$media->sites()->sync($site_ids);

			$pg_ids = array_merge($media->publishing_groups()->allRelatedIds()->toArray(), $request->get('publishing_group_ids'));
			$media->publishing_groups()->sync($pg_ids);

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
	 * Rather than deleting Media objects, this removes the specified Site/PublishingGroup associations.
	 * From a users' perspective, the Media has been removed from the system.
	 *
	 * @param  Request    $request
	 * @param  Definition $definition
	 * @return SymfonyResponse
	 */
	public function destroy(DeleteRequest $request, Media $media){
		$this->authorizeAll($request, 'delete', $media); // Ensures that the user can sync with Sites / Practice Groups

		if($request->has('site_ids')){
			$site_ids = array_diff($media->sites()->allRelatedIds()->toArray(), $request->get('site_ids'));
			$media->sites()->sync($site_ids);
		}

		if($request->has('publishing_group_ids')){
			$pg_ids = array_diff($media->publishing_groups()->allRelatedIds()->toArray(), $request->get('publishing_group_ids'));
			$media->publishing_groups()->sync($pg_ids);
		}

		return (new SymfonyResponse())->setStatusCode(200);
	}


	/**
	 * Ensures that the user can write to the provided Site / PublishingGroup IDs
	 * @return void
	 */
	protected function authorizeAll(Request $request, $action, Media $media)
	{
		$acos = new Collection;

		if($request->has('site_ids')){
			$acos = $acos->merge(Site::whereIn('id', $request->get('site_ids'))->get());
		}

		if($request->has('publishing_group_ids')){
			$acos = $acos->merge(PublishingGroup::whereIn('id', $request->get('publishing_group_ids'))->get());
		}

		foreach($acos as $model){
			$this->authorize($action, [ $media, $model ]);
		}
	}

}
