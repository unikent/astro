<?php
namespace Astro\API\Transformers\Api\v1;

use Astro\API\Models\Revision;
use League\Fractal\TransformerAbstract as FractalTransformer;

/**
 * Transforms a Revision object its json representation for the API.
 * Can optionally include the blocks and options.
 * @package Astro\API\Transformers\Api\v1
 */
class RevisionTransformer extends FractalTransformer
{

    protected $availableIncludes = [];

    protected $full = false;

    /**
     * Create a RevisionTransformer
     * @param bool $full If true, then returns the full revision details including blocks, otherwise false.
     */
    public function __construct($full = false)
    {
        $this->full = $full;
    }

	public function transform(Revision $revision)
	{
	    $data = [
	        'id' => $revision->id,
            'title' => $revision->title,
            'version' => $revision->version,
            'created_at' => $revision->created_at ? $revision->created_at->toDateTimeString() : null,
            'updated_at' => $revision->updated_at ? $revision->updated_at->toDateTimeString() : null,
            'layout' => [
                'name' => $revision->layout_name,
                'version' => $revision->layout_version
            ]
        ];
	    if($this->full){
	        $data['options'] = $revision->options;
	        $data['blocks'] = $revision->blocks;
        }
        return $data;
	}

}
