<?php
namespace App\Models;

use DB;
use Exception;
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
        'path'
	];

	protected $hidden = [
		'lft',
		'rgt'
	];

    protected $scoped = ['site_id'];

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

	public function draft_page()
	{
		return $this->belongsTo(PageContent::class, 'draft_id');
	}

	public function published_page()
	{
	    return $this->belongsTo( Revision::class, 'published_revision_id' );
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
    public function scopeForSiteAndPath($query, $site_id, $path)
    {
        return $query->where('site_id', $site_id)
                    ->where('path', $path);
    }

    public function findBySiteAndPath($site_id, $path)
    {
        return $this->forSiteAndPath($site_id,$path)->first();
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
     * Scope a query to only include active routes.
     *
     * @param Builder $query
     * @param boolean $value
     * @return Builder
     */
	public function scopeActive(Builder $query, $value = true)
	{
		return $query->whereNotNull('published_revision_id');
	}

	/**
	 * Returns true if is_active is true, else false
	 * @return boolean
	 */
	public function isActive()
	{
		return null != $this->published_revision_id;
	}

	/**
	 * Returns true if site_id is set, else false
	 * @return boolean
	 */
	public function isSite()
	{
	    return $this->isRoot();
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
	 * Clones the desendants of the given Route to the current Route instance.
	 *
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
     * @param null|PageContent $pagecontent
     */
    public function setDraft($pagecontent)
    {
        $this->draft_id = $pagecontent ? $pagecontent->id : null;
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
