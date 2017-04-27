<?php
namespace App\Models;

use DB;
use Exception;
use Baum\Node as BaumNode;

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

			$this->is_canonical = true;
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
}
