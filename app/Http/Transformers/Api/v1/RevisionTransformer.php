<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Revision;
use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Item as FractalItem;

class RevisionTransformer extends FractalTransformer
{

    protected $availableIncludes = ['pagecontent'];



	public function transform(Revision $revision)
	{
	    $unbake = json_decode($revision->bake, true);
	    $unbake = $unbake['data'];
	    $data = [
	        'id' => $revision->id,
            'title' => $unbake['title'],
            'page_content_id' => $revision->page_content_id,
            'type' => $revision->type,
            'created_at' => $revision->created_at,
            'updated_at' => $revision->updated_at,
            'layout_name' => $unbake['layout_name'],
            'layout_version' => $unbake['layout_version']
        ];
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
