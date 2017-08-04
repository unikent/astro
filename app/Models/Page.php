<?php
namespace App\Models;

use Baum\Node;
use DB;
use Exception;
use App\Exceptions\PageExistsException;

use Baum\Node as BaumNode;
use App\Models\Traits\Routable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Contracts\Routable as RoutableContract;

class Page extends BaumNode implements RoutableContract
{
	use Routable;

	public $timestamps = false;

	protected $fillable = [
		'slug',
		'draft_id',
		'parent_id',
        'site_id',
        'version',
        'path'
	];

	protected $hidden = [
		'lft',
		'rgt'
	];

    protected $scoped = ['site_id','version'];

    // The draft state of this page.
    const STATE_NEW = 'new';  // not published
    const STATE_DRAFT = 'modified'; // modified since last published
    const STATE_DELETED = 'deleted'; // deleted since last published
    const STATE_MOVED = 'moved'; // moved since last published
    const STATE_PUBLISHED = 'published'; // not modified since last published
    const STATE_EMPTY = 'empty'; // no draft or published state.

    /**
     * Prunes this Page and its descendants removing any pages with no draft or published revision.
     */
    public function removeEmptyPages()
    {
        if(!$this->draft_id && !$this->published_id){
            $this->delete();
        }else {
            foreach ($this->children as $child) {
                $child->removeEmptyPages();
            }
        }
    }

    /**
     * Get the draft state of this Page
     * @return string
     */
    public function draftState()
    {
        if($this->draft_id == $this->published_id){
            if(!$this->draft_id){
                return self::STATE_EMPTY;
            }else {
                return self::STATE_PUBLISHED;
            }
        }elseif(!$this->published_id){
            return self::STATE_NEW;
        }elseif(!$this->draft_id){
            return self::STATE_DELETED;
        }elseif($this->draft->pagecontent_id == $this->published->pagecontent_id){
            return self::STATE_DRAFT;
        }else{
            return self::STATE_MOVED;
        }
    }

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

	public function site()
	{
		return $this->belongsTo(Site::class, 'site_id');
	}

	public function draft()
	{
		return $this->belongsTo(Revision::class, 'draft_id');
	}

	public function published()
	{
	    return $this->belongsTo( Revision::class, 'published_id' );
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
     * Scope a query to only include pages with published content.
     *
     * @param Builder $query
     * @param boolean $value
     * @return Builder
     */
    public function scopePublished(Builder $query, $value = true)
    {
        return $query->whereNotNull('published_id');
    }

    /**
     * Scope a query to only include active routes.
     *
     * @param Builder $query
     * @param boolean $value
     * @deprecated
     * @return Builder
     */
    public function scopeActive(Builder $query, $value = true)
    {
        return $this->scopePublished($query, $value);
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

    public static function findByHostAndPath($host, $path)
    {
    }

    /**
     * Delete the model from the database.
     * Creates a new Redirect model to ensure that the path is not lost forever.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
    	DB::beginTransaction();

    	try {
    		if($this->isActive()){
    			Redirect::createFromRoute($this);
    		}

	    	parent::delete();

	    	DB::commit();
    	} catch(Exception $e){
    		DB::rollback();
    		throw $e;
    	}
    }



	/**
     * Has this page been published?
	 * @return boolean
     * @deprecated
	 */
	public function isActive()
	{
		return $this->isPublished();
	}

    /**
     * Has this page been published?
     * @return bool
     */
	public function isPublished()
    {
        return (null != $this->published_id);
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
     * Set the destination Page's draft to our draft
     * Set the source Page's draft to null.
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
     * Does this route have a draft?
     * @return bool
     */
	public function hasDraft()
    {
        return $this->draft_id ? true : false;
    }

    /**
     * Set the draft version of this route.
     * @param null|Revision $revision
     */
    public function setDraft($revision)
    {
        $this->draft_id = $revision ? $revision->id : null;
        $this->save();
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
