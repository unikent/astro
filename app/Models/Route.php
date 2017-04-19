<?php
namespace App\Models;

use DB;
use Exception;
use Baum\Node as BaumNode;

class Route extends BaumNode
{
	public $timestamps = false;

	protected $fillable = [ 'slug' ];

	protected $hidden = [ 'lft', 'rgt'];


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

	public function page()
	{
		return $this->belongsTo(Page::class, 'page_id');
	}

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
