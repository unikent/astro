<?php
namespace App\Models;

use App\Http\Transformers\Api\v1\BlockTransformer;
use App\Models\Scopes\VersionScope;
use DB;
use Doctrine\DBAL\Version;
use Exception;
use App\Models\Definitions\Layout as LayoutDefinition;
use App\Http\Transformers\Api\v1\PageTransformer;
use Baum\Node as BaumNode;
use Illuminate\Database\Eloquent\Collection;

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
        return $query->withoutGlobalScope(Version::class);
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
        $this->save();
        return $this;
    }

    /**
     * Get the child of this route with the given slug.
     * @param string $slug The slug of the route to retrieve.
     * @return Page The child Route with the given slug or null.
     */
    public function getChildWithSlug($slug)
    {
        return $this->immediateDescendants()->where('slug', $slug)->first();
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
    /**
     * Creates a PublishedPage by baking the Page to JSON with Fractal.
     * If the Page is dirty, an exception will be thrown.
     *
     * @param \League\Fractal\TransformerAbstract $transformer
     * @return void
     * @throws Exception
     */
    public function publish(FractalTransformer $transformer)
    {
        if($this->isDirty()){
            throw new Exception('You cannot publish a pagecontent with unsaved changes.');
        }

        // Ensure that the draft does not have unpublished ancestors
        if($this->hasUnpublishedAncestors() ) {
            throw new UnpublishedParentException('Page cannot be published: it has unpublished ancestors.');
        }

        DB::beginTransaction();

        try {
            // If there is a draft route, publish it
            if($this->draftRoute) $this->draftRoute->makeActive();

            // Ensure that important relations are populated and up-to-date
            $this->load([ 'blocks', 'draftRoute', 'activeRoute' ]);

            // Create our Revision, bake it with Fractal
            $published = new Revision;
            $published->page_content_id = $this->getKey();
            $published->blocks = fractal($this, $transformer)->parseIncludes([ 'blocks', 'activeRoute' ])->toJson();
            $published->save();

            DB::commit();
        } catch(Exception $e){
            DB::rollback();
            throw $e;
        }

        $this->load([ 'draftRoute', 'activeRoute' ]);
    }

    public function hasUnpublishedAncestors()
    {
        return true;
    }

    /**
     * Restores a PageContent from a Revision instance. All Block instances are
     * replaced with those defined by the bake. Routes remain unaltered.
     *
     * @return void
     * @throws Exception
     */
    public function revert(Revision $published){
        if($this->getKey() !== $published->page_content_id){
            throw new Exception('Revision must be related to this PageContent');
        }

        if($this->isDirty()){
            throw new Exception('A PageContent must be in a clean state in order to revert.');
        }

        DB::beginTransaction();

        try {
            $baked = json_decode($published->bake, TRUE);
            $baked = $baked['data'];

            // Restore the Page object
            $this->fill($baked);
            $this->save();

            // Restore the Block instances
            $this->blocks()->delete();

            if(isset($baked['blocks'])){
                foreach($baked['blocks'] as $region => $blocks){
                    foreach($blocks as $data){
                        $block = new Block;
                        $block->page_content_id = $this->getKey();
                        $block->fill($data);
                        $block->save();
                    }
                }
            }

            DB::commit();
        } catch(Exception $e){
            DB::rollback();
            throw $e;
        }

        $this->load([ 'blocks', 'routes', 'draftRoute', 'activeRoute' ]);
    }





    /**
     * Clones the desendants of the given Page to the current Page instance.
     * @param Page $node
     * @return \Illuminate\Database\Eloquent\Collection $descendants
     */
    public function cloneDescendants(Page $node)
    {
        $tree = [];

        // Ensure that existing descendants are present in the tree
        $descendants = $this->descendants()->get()->toHierarchy();

        if(!$descendants->isEmpty()){
            $this->replicateIterator($descendants, $tree, true);
        }

        // Clone the new descendants into the tree
        $descendants = $node->descendants()->get()->toHierarchy();

        if(!$descendants->isEmpty()){
            $this->replicateIterator($descendants, $tree, false);
        }

        // Persist
        $foobar = $this->makeTree($tree);

        $model = $this->fresh();
        return $model->descendants()->get();
    }

    /**
     * Recursive iterator used by replicateWithDescendants.
     *
     * @param  Collection $nodes
     * @param  array|null &$output
     * @para
     */
    protected function replicateIterator(Collection $nodes, array &$output, $preserve)
    {
        foreach($nodes as $node){
            $data = array_except($node->toArray(), [ 'parent_id', 'depth', 'lft', 'rgt', 'children', 'is_canonical' ]);

            if(!$preserve){
                $data = array_except($data, [ 'id', 'path', 'is_active' ]);
            }

            if(!$node->children->isEmpty()){
                $data['children'] = [];
                $this->replicateIterator($node->children, $data['children'], $preserve);
            }

            $output[] = $data;
        }
    }


}
