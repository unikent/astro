<?php
namespace App\Models;

use App\Models\Page;
use App\Models\PublishingGroup;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{

    public $fillable = [
        'name',
        'publishing_group_id',
        'host',
        'path',
        'created_by',
        'updated_by',

        'options'
    ];

    protected $casts = [
        'options' => 'json'
    ];

    protected $definition = null;

    /************************************************************
     * Relationships
     ************************************************************/

    /**
     * The homepage for a version of this site. Defaults to DRAFT.
     * @param string $version The version of the site to get the homepage for. Defaults to Page::STATE_DRAFT
     * @return mixed
     */
    public function homepage($version = Page::STATE_DRAFT)
    {
        return $this->hasOne(Page::class, 'site_id')
            ->whereNull('parent_id')
            ->where('version', $version);
    }

    /**
     * All the pages of a version of this site.
     * @param string $version The version of the site to get the homepage for. Defaults to Page::STATE_DRAFT
     * @return mixed
     */
    public function pages($version = Page::STATE_DRAFT)
    {
        return $this->hasMany(Page::class, 'site_id')
                    ->where('version', $version);
    }

    /**
     * All the draft pages for this site.
     * @return mixed
     */
    public function draftPages()
    {
        return $this->pages(Page::STATE_DRAFT);
    }

    /**
     * All the published pages for this site.
     * @return mixed
     */
    public function publishedPages()
    {
        return $this->hasMany(Page::class, 'site_id')
                    ->where('published_id');
    }

    /**
     * The publishing group for this site.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
	public function publishing_group()
	{
		return $this->belongsTo(PublishingGroup::class, 'publishing_group_id');
	}


}
