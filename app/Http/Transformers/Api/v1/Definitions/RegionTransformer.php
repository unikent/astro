<?php
namespace App\Http\Transformers\Api\v1\Definitions;

use App\Models\Definitions\Region;
use League\Fractal\TransformerAbstract as FractalTransformer;

class RegionTransformer extends FractalTransformer
{

	public function transform(Region $region)
	{
		return $region->toArray();
	}

}
