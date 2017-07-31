<?php

namespace App\Models;

use App\Http\Transformers\Api\v1\PageContentTransformer;
use App\Models\Traits\Tracked;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
	use Tracked;

	protected $fillable = [
		'page_content_id',
		'bake',
	];

	public function pagecontent()
	{
		return $this->belongsTo(PageContent::class, 'page_content_id');
	}

    /**
     * Create a new Revision based on some existing page content.
     * @param PageContent $content The PageContent to create it from.
     * @param string $type The type of this revision (draft, published, etc)
     */
	public static function createFromPageContent(PageContent $content, Authenticatable $user, $type = 'draft')
    {
        $revision = new Revision();
        $revision->page_content_id = $content->id;
        $revision->bake = fractal(
            $content,
            new PageContentTransformer()
        )->parseIncludes([ 'blocks', 'activeRoute' ])->toJson();
        $revision->type = $type;
        $revision->updated_by = $user->getAuthIdentifier();
        $revision->created_at = $content->created_at;
        $revision->created_by = $content->created_by;
        return $revision;
    }

}
