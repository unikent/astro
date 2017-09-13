<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\PublishingGroup;
use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Collection as FractalCollection;

class PublishingGroupTransformer extends FractalTransformer
{

    protected $availableIncludes = [];

    /**
     * Transform a PublishingGroup into an array representation as returned by the API
     * @param PublishingGroup $group
     * @return array
     */
	public function transform(PublishingGroup $group)
	{
		return [
		  'id' => $group->id,
            'name' => $group->name
        ];
	}

    /**
     * Include all sites associated with this publishing group.
     * @param PublishingGroup $group The PublishingGroup
     * @return FractalCollection
     */
    public function includeSites(PublishingGroup $group)
    {
        if(!$group->sites->isEmpty()){
            return new FractalCollection($group->sites, new SiteTransformer, false);
        }
    }

    /**
     * Include users associated with the publishing group.
     * @param PublishingGroup $group The PublishingGroup
     * @return FractalCollection
     */
    public function includeUsers(PublishingGroup $group)
    {
        if(!$group->users->isEmpty()){
            return new FractalCollection($group->users, new UserTransformer, false);
        }
    }
}
