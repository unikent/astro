<?php
namespace App\Models;

use Baum\Node;
use DB;
use Exception;
use App\Exceptions\PageExistsException;

use Baum\Node as BaumNode;
use Illuminate\Database\Eloquent\Collection;

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

		static::saving(function($node){
			$node->path = $node->generatePath();
		});
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
     * All the revisions attached to this Page.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function history()
    {
        return $this->hasMany(Revision::class);
    }

    /**
     * The current Revision attached to this Page.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function revision()
    {
        return $this->belongsTo(Revision::class, 'revision_id');
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
        return $query->where('version', $version);
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
     * Move logic:
     * If parent does not have a node with this path, create it.
     * Check that the node we are copying to does not already have a draft
     * Repeat for all children.
     * Prune any now-empty Pages.
     * @param $parent
     */
	public function move($parent)
    {
        // No reordering within a parent
        if( $this->parent_id == $parent->id ) {
            return $this;
        }
        $dest = $parent->children->where('slug', $this->slug)->first();
        if(!$dest){
            $dest = $parent->children()->create([
                'site_id' => $parent->site_id,
                'slug' => $this->slug,
                'parent_id' => $parent->id
            ]);
        }
        // can't move if it already exists
        if($dest->draft_id){
            throw new PageExistsException($dest);
        }
        $dest->setDraft($this->draft);
        $dest->save();
        $this->setDraft(null);
        foreach($this->children as $child){
            $child->move($dest);
        }
        $this->removeEmptyPages();
        return $dest;
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

    /**
     * Set the revision for this page.
     * @param null|Revision $revision
     */
    public function setRevision($revision)
    {
        $this->revision_id = $revision ? $revision->id : null;
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
}
