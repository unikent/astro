<?php
namespace App\Models;

use DB;
use Exception;
use Baum\Node as BaumNode;
use App\Models\Traits\Routable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Contracts\Routable as RoutableContract;

class Route extends BaumNode implements RoutableContract
{
	use Routable;

	public $timestamps = false;

	protected $fillable = [
		'slug',
		'page_id',
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

	public function draft()
	{
		return $this->belongsTo(Page::class, 'page_id');
	}

	public function published_page()
	{
	    return $this->belongsTo( PublishedPage::class, 'published_page-id' );
	}


    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
    	//DB::beginTransaction();
        return parent::save($options);

    	try {
    		if(!$this->isActive()){
				static::where('page_id', $this->page_id)
					->where($this->getKeyName(), '!=', $this->getKey())
					->active(false)
					->delete();
    		}

	    	parent::save($options);

	    	DB::commit();
	    	return true;
    	} catch(Exception $e){
    		DB::rollback();
    		return false;
    	}
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
		return $query->where('is_active', '=', $value);
	}

	/**
	 * Returns true if is_active is true, else false
	 * @return boolean
	 */
	public function isActive()
	{
		return $this->is_active;
	}

	/**
	 * Sets is_active only if the value is falsey. Enforces use of makeActive()
	 * by throwing an Exception if a truthy value is set.
	 *
	 * @param $value
	 * @throws Exception
	 */
	public function setIsActiveAttribute($value){
		throw new Exception('The is_active property cannot be set directly. Please use makeActive.');
	}

	/**
	 * Sets is_active to true on this instance, and deletes all
	 * other Routes to the same Page. Delete ensures that a Redirect is created.
	 *
	 * @return void
	 */
	public function makeActive()
	{
		DB::beginTransaction();

		try {
			$this->attributes['is_active'] = true;
			$this->save();

			static::where('page_id', $this->page_id)
				->where($this->getKeyName(), '!=', $this->getKey())
				->each(function($model){ $model->delete(); }); // We take this approach as we want model lifecycle events.

			DB::commit();
		} catch(Exception $e){
			DB::rollback();
			throw $e;
		}
	}



	/**
	 * Ensures that this Route, and all Routes sharing the same page_id,
	 * are marked as sites.
	 *
	 * @return void
	 */
	public function makeSite($site_or_id)
	{
		$site_id = is_a($site_or_id, Site::class) ? $site_or_id->getKey() : $site_or_id;

		DB::beginTransaction();

		try {
			static::where('page_id', '=', $this->page_id)
				->update([ 'site_id' => $site_id ]);

			$this->site_id = $site_id;
			$this->save();

			DB::commit();
		} catch(Exception $e){
			DB::rollback();
			throw $e;
		}
	}

	/**
	 * Returns true if site_id is set, else false
	 * @return boolean
	 */
	public function isSite()
	{
		return !empty($this->site_id);
	}


	/**
	 * Assembles a path using the ancestor slugs within the Route tree
	 * @return string
	 */
	public function generatePath()
	{
		if(!$this->parent_id && $this->slug){
			throw new Exception('A root Route cannot have a slug.');
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
	 * @param Route $node
	 * @return \Illuminate\Database\Eloquent\Collection $descendants
	 */
	public function cloneDescendants(Route $node)
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
        return $this->draft ? true : false;
    }

    /**
     * Set the draft version of this route.
     * @param null|Page $page
     */
    public function setDraft($page)
    {
        $this->page_id = $page ? $page->id : null;
    }

    /**
     * Get the child of this route with the given slug.
     * @param string $slug The slug of the route to retrieve.
     * @return Route The child Route with the given slug or null.
     */
    public function getChildWithSlug($slug)
    {
        return $this->immediateDescendants()->where('slug', $slug)->first();
    }
}
