<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Media;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\TransformerAbstract as FractalTransformer;
use App\Http\Transformers\Api\v1\Definitions\MediaTransformer as MediaDefinitionTransformer;

class MediaTransformer extends FractalTransformer
{

	public function transform(Media $media)
	{
		return $media->toArray();
	}

}
