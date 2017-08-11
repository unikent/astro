<?php

namespace App\Models;

use App\Http\Transformers\Api\v1\PageContentTransformer;
use App\Models\Traits\Tracked;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Revisions track the state of a Page, including its content, options, title, etc at a point in
 * time.
 * @package App\Models
 */

class Revision extends Model
{
	use Tracked;

	const TYPE_DRAFT = 'draft';
	const TYPE_PUBLISHED = 'published';
	const TYPE_DELETED = 'deleted';
	const TYPE_AUTOSAVE = 'autosave';

	protected $fillable = [
        'site_id',
        'title',
        'created_by',
        'updated_by',
        'layout_name',
        'layout_version',
		'bake'
	];


    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function($revision){
            // bake cannot be null
            $revision->bake = !empty($revision->bake) ? $revision->bake : '[]';
            if(!$revision->revision_set_id){
                throw new ValidationException("Revision must be part of a RevisionSet");
            }
        });
    }

    public function draftPage()
    {
        return $this->hasOne(Page::class, 'revision_id')->where('version', Page::STATE_DRAFT);
    }

    public function publishedPage()
    {
        return $this->hasOne(Page::class, 'revision_id')->where('version', Page::STATE_PUBLISHED);
    }

    /**
     * Create a new Revision based on some existing page content.
     * @param PageContent $content The PageContent to create it from.
     * @param string $type The type of this revision (draft, published, etc)
     */
	public static function createFromPageContent(PageContent $content, Authenticatable $user, $type = 'draft')
    {
        $revision = new Revision();
        $revision->title = $content->title;
        $revision->layout_name = $content->layout_name;
        $revision->layout_version = $content->layout_version;
        $revision->page_content_id = $content->id;
        $revision->bake = fractal(
            $content,
            new PageContentTransformer()
        )->parseIncludes([ 'blocks', 'activeRoute' ])->toJson();
        $revision->type = $type;
        $revision->updated_by = $user->id;
        $revision->created_at = $content->created_at;
        $revision->created_by = $content->created_by;
        return $revision;
    }

}
