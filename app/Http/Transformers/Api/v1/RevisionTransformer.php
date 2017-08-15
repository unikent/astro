<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Revision;
use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Item as FractalItem;

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
            'revision_set_id' => $revision->revision_set_id,
            'type' => $revision->type,
            'created_at' => $revision->created_at ? $revision->created_at->toDateTimeString() : null,
            'updated_at' => $revision->updated_at ? $revision->updated_at->toDateTimeString() : null,
            'layout_name' => $revision->layout_name,
            'layout_version' => $revision->layout_version
        ];
	    if($this->full){
            $unbake = json_decode($revision->bake, true);
            $unbake = $unbake['data'];
	        $data['bake'] = $unbake;
        }
        return $data;
	}

}
