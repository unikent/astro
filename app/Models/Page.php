<?php
namespace App\Models;

use App\Exceptions\UnpublishedParentException;
use App\Models\Scopes\VersionScope;
use DB;
use Doctrine\DBAL\Version;
use Exception;
use App\Models\Definitions\Layout as LayoutDefinition;
use Baum\Node as BaumNode;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\TransformerAbstract;

/**
 * A Page represents a path in a hierarchical site structure.
 * Each page has a current revision, and each "tree" of pages is scoped by its site_id and version (draft, published, etc).
 *
 * By default, a global Eloquent scope is applied to Pages which restricts all queries to "draft" versions.
 *
 * Add the scope "anyVersion" to a query to remove this restriction.
 * @package App\Models
 */
class Page extends BaumNode
{
	public $table = 'pages';

	public $timestamps = false;

	protected $fillable = [
		'slug',
		'draft_id',
		'parent_id',
        'site_id',
        'version',
        'path',
        'revision_id'
	];

	protected $hidden = [
		'lft',
		'rgt'
	];

	// nested set implementation has a tree for each site_id+version combination.
    protected $scoped = ['site_id','version'];

    // The draft state of this page.
    const STATE_NEW = 'new';  // not published
    const STATE_DRAFT = 'draft'; // modified since last published
    const STATE_DELETED = 'deleted'; // deleted since last published
    const STATE_MOVED = 'moved'; // moved since last published
    const STATE_PUBLISHED = 'published'; // not modified since last published
    const STATE_EMPTY = 'empty'; // no draft or published state.


    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct($attributes = []){
        parent::__construct($attributes);

        $this->parent_id = $this->parent_id ?: null;
    }


	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();

		// restrict requests to the draft pages.
        // or not as it appears to mess things up elsewhere...
		static::addGlobalScope(new VersionScope());

		static::saving(function($node){
			$node->path = $node->generatePath();
		});
	}

    /**
     * Generate the blocks array for this page.
     * @return array
     */
	public function bake()
    {
        $data = [];
        $blocksByRegion = $this->blocks()
                                ->with('media')
                                ->orderBy('order')
                                ->get()
                                ->groupBy('region_name');
        $this->load('blocks.media');
        foreach($blocksByRegion as $region => $blocks){
            $data[$region] = [];
            foreach($blocks as $block){
                $block->embedMedia();
                $data[$region][] = [
                    'id' => $block->id,
                    'definition_name' => $block->definition_name,
                    'definition_version' => $block->definition_version,
                    'region_name' => $block->region_name,
                    'fields' => $block->fields
                ];
            }
        }
        return $data;
    }

    /************************************************************************
     * Relations
     ************************************************************************/

    /**
     * The Site that this Page belongs to.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
	public function site()
	{
		return $this->belongsTo(Site::class, 'site_id');
	}

    /**
     * The current Revision attached to this Page.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function revision()
    {
        return $this->belongsTo(Revision::class, 'revision_id');
    }

    /**
     * The Blocks linked to this Page.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blocks()
    {
        return $this->hasMany(Block::class, 'page_id');
    }

    /************************************************************************
     * Query Scopes
     ************************************************************************/

    /**
     * Restrict query to draft version of the site.
     * @param $query
     * @return mixed
     */
	public function scopeDraft($query)
    {
        return $this->scopeVersion($query, self::STATE_DRAFT);
    }

    /**
     * Restrict query to published version of the site.
     * @param $query
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $this->scopeVersion($query, self::STATE_PUBLISHED);
    }

    /**
     * Restrict query to specific version of the site.
     * @param $query
     * @return mixed
     */
    public function scopeVersion($query, $version)
    {
        return $query->withoutGlobalScope(VersionScope::class)
                    ->where('version', $version);
    }

    /**
     * Removes the default drafts-only global scope for the query.
     * @param $query
     * @return mixed
     */
    public function scopeAnyVersion($query)
    {
        return $query->withoutGlobalScope(VersionScope::class);
    }

    /**
     * Restrict query to specific site.
     * @param $query
     * @param $site_id
     * @return mixed
     */
    public function scopeforSite($query, $site_id)
    {
        return $query->where('site_id', $site_id);
    }

    /**
     * Restrict query to page on site with specific path.
     * @param $query
     * @param $site_id
     * @param $path
     * @return mixed
     */
    public static function scopeForSiteAndPath($query, $site_id, $path)
    {
        return $query->where('site_id', $site_id)
                    ->where('path', $path);
    }


    /**************************************************************************
     * Utility Methods
     */
    public function publishedVersion()
    {
        if(Page::STATE_PUBLISHED == $this->version){
            return $this;
        }
        return Page::published()
                    ->forSiteAndPath($this->site_id, $this->path)
                    ->first();
    }

    /**
     * Find a Page by site id and path.
     * @param $site_id
     * @param $path
     * @return mixed
     */
    public static function findBySiteAndPath($site_id, $path)
    {
        return Page::forSiteAndPath($site_id,$path)->first();
    }

    /**
     * Find a Page based on host (domain name) and path.
     * @param $host
     * @param $path
     * @return $this
     */
    public static function findByHostAndPath($host, $path, $version = Page::STATE_PUBLISHED)
    {
        $query = Page::version($version)
            ->join('sites', 'site_id', '=', 'sites.id')
            ->where('sites.host', $host)
            ->where(function($query) use($path) {
                $query->whereRaw("concat(sites.path, pages.path) = ?", [$path])
                      ->orWhereRaw("concat(sites.path, pages.path) = ?", [$path.'/']);
            });
        return $query->first();
    }

	/**
	 * Assembles a path using the ancestor slugs within the Route tree
	 * @return string
	 */
	public function generatePath()
	{
		if(!$this->parent_id && $this->slug){
			throw new Exception('A root Page cannot have a slug.');
		}

		$path = '/';

		$chain = $this->parent_id ? $this->parent->ancestorsAndSelf([ 'slug' ])->get() : [];

		foreach($chain as $ancestor){
			if(empty($ancestor->slug)) continue; // If there are any ancestors without a path, skip.
			$path .= $ancestor->slug . '/';
		}

		return $path . $this->slug;
	}


    /**
     * Set the revision for this page.
     * @param null|Revision $revision
     */
    public function setRevision($revision)
    {
        $this->revision_id = $revision ? $revision->id : null;
        if($revision && !$revision->blocks){
            $revision->blocks = $this->bake();
            $revision->save();
        }
        if($this->isPublishedVersion()){
            $revision->setPublished();
        }
        $this->save();
        return $this;
    }

    /**
     * Is this a published version of a page?
     * @return bool
     */
    public function isPublishedVersion()
    {
        return Page::STATE_PUBLISHED == $this->version;
    }

    /**
     * Loads the Layout definition, optionally including Regions
     *
     * @param boolean $includeRegions
     * @return void
     */
    public function loadLayoutDefinition($includeRegions = false)
    {
        $file = LayoutDefinition::locateDefinition($this->revision->layout_name, $this->revision->layout_version);
        $definition = LayoutDefinition::fromDefinitionFile($file);

        if($includeRegions) $definition->loadRegionDefinitions();

        $this->layoutDefinition = $definition;
    }

    /**
     * Returns the layoutDefinitions Collection, loading from disk if necessary,
     * optionally including Regions.
     *
     * @param boolean $includeRegions
     * @return LayoutDefinition
     */
    public function getLayoutDefinition($includeRegions = false){
        if(!$this->layoutDefinition){
            $this->loadLayoutDefinition($includeRegions);

        } elseif($includeRegions) {
            // If using a previously-loaded $layoutDefinition, region definitions may not be present.
            // By calling getRegionDefinitions rather than loadRegionDefinitions, RegionDefinitions get loaded,
            // but only if they are not already present. A call to laodRegionDefinitions would force a new load
            // operation regardless.
            $this->layoutDefinition->getRegionDefinitions();
        }

        return $this->layoutDefinition;
    }


    /**
     * Deletes all blocks in the given Region
     *
     * @param  string $region
     * @return void
     */
    public function clearRegion($region){
        Block::deleteForPageRegion($this, $region);
    }



}
