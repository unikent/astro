<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Revision;
use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Item as FractalItem;

class RevisionTransformer extends FractalTransformer
{

    protected $availableIncludes = ['pagecontent'];

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
            'page_content_id' => $revision->page_content_id,
            'type' => $revision->type,
            'created_at' => $revision->created_at,
            'updated_at' => $revision->updated_at,
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


    /**
     * Include associated Site
     * @return FractalItem
     */
    public function includePagecontent(Revision $revision)
    {
//        if($revision->site){
            return new FractalItem($revision->pagecontent, new PageContentTransformer, false);
 //       }
    }
}
