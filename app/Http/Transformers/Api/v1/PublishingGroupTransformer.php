<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\PublishingGroup;
use League\Fractal\TransformerAbstract as FractalTransformer;

class PublishingGroupTransformer extends FractalTransformer
{

    protected $availableIncludes = [];

	public function transform(PublishingGroup $group)
	{
		return [
		  'id' => $group->id,
            'name' => $group->name
        ];
	}

}
