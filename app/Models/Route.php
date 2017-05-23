<?php
namespace App\Models;

use DB;
use Exception;
use Baum\Node as BaumNode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Route extends BaumNode
{
	public $timestamps = false;

	protected $fillable = [
		'slug',
		'parent_id',
	];

	protected $hidden = [
		'lft',
		'rgt'
	];

	protected $casts = [
        'is_active' => 'boolean',
        'is_canonical' => 'boolean',
	];


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

	public function page()
	{
		return $this->belongsTo(Page::class, 'page_id');
	}

	public function published_page()
	{
		return $this->hasOne(PublishedPage::class, 'page_id', 'page_id')->latest()->limit(1);
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
	 * Sets is_active to true on this route, and destroys any other routes
	 * that are not yet active.
	 *
	 * @return void
	 */
	public function makeActive()
	{
		DB::beginTransaction();

		try {
			$this->attributes['is_active'] = true;
			$this->save();

			static::where('page_id', $this->page_id)->active(false)->delete();

			DB::commit();
		} catch(Exception $e){
			DB::rollback();
			throw $e;
		}
	}


    /**
     * Scope a query to only include canonical routes.
     *
     * @param Builder $query
     * @param boolean $value
     * @return Builder
     */
	public function scopeCanonical(Builder $query, $value = true)
	{
		return $query->where('is_canonical', '=', $value);
	}

	/**
	 * Ensures that is_canonical cannot be set directly.
	 *
	 * @param $value
	 * @throws Exception
	 */
	public function setIsCanonicalAttribute($value){
		throw new Exception('The is_canonical attribute cannot be set directly. Use makeCanonical.');
	}

	/**
	 * Sets is_canonical to true on this route, and sets is_canonical to
	 * false on all routes sharing the same Page destination.
	 *
	 * @return void
	 */
	public function makeCanonical()
	{
		DB::beginTransaction();

		try {
			static::where('page_id', '=', $this->page_id)
				->where('is_canonical', '=', true)
				->update([ 'is_canonical' => false ]);

			$this->attributes['is_canonical'] = true;
			$this->save();

			DB::commit();
		} catch(Exception $e){
			DB::rollback();
			throw $e;
		}
	}

	/**
	 * Returns true if is_canonical is true, else false
	 * @return boolean
	 */
	public function isCanonical()
	{
		return $this->is_canonical;
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
	 * Attempts to retrieve a Route by path
	 *
	 * @param  string $hash
	 * @return Route
	 */
	public static function findByPath($path)
	{
		return static::where('path', '=', $path)->first();
	}

	/**
	 * Attempts to retrieve a Route by path, throws Exception when not found
	 *
	 * @param  string $path
	 * @return Route
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public static function findByPathOrFail($path)
	{
		return static::where('path', '=', $path)->firstOrFail();
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
		$this->makeTree($tree);

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
			$data = array_except($node->toArray(), [ 'parent_id', 'depth', 'lft', 'rgt', 'children' ]);

			if(!$preserve){
				$data = array_except($data, [ 'id', 'path', 'is_active', 'is_canonical' ]);
			}

			if(!$node->children->isEmpty()){
				$data['children'] = [];
				$this->replicateIterator($node->children, $data['children'], $preserve);
			}

			$output[] = $data;
		}
	}

}
