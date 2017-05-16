<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\PublishedPage;
use League\Fractal\TransformerAbstract as FractalTransformer;

class PublishedPageTransformer extends FractalTransformer
{

    protected $availableIncludes = [];

	public function transform(PublishedPage $pp)
	{
		return array_merge($pp->toArray(), [
            'bake' => json_decode($pp->bake, TRUE), // TODO: This seems wasteful, but we don't want Fractal to double-encode... do we?
        ]);
	}

}
