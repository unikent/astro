<?php

namespace App\Models;

use App\Models\Block;
use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
	use Tracked;

	protected $fillable = ['parent_block', 'order', 'section', 'type', 'fields', 'title', 'slug'];
	protected $visible = ['title', 'options','slug', 'path', 'structure', 'id', 'order', 'parent_block', 'section', 'created_by', 'created_at', 'title'];
	protected $appends = ['slug', 'path', 'structure', 'parent'];

	public function route()
	{
		return $this->hasOne('App\Models\Route', 'page_id');
	}

	public function blocks()
	{
		return $this->hasMany('App\Models\Block', 'page_id')->orderBy('parent_block')->orderBy('order');
	}

	public function save(array $options = [])
	{
		parent::save();

		if($this->route)
		{
			$this->route->page_id = $this->id;
			$this->route->save();
		}

	}

	public function getOptionsAttribute()
	{
		return !empty($this->attributes['options']) ? json_decode($this->attributes['options'], true) : null;
	}

	public function setOptionsAttribute($json)
	{
		$this->attributes['options'] = json_encode($json);
	}

	public function getSlugAttribute()
	{
		return $this->route->slug;
	}

	public function getPathAttribute()
	{
		return $this->route->path;
	}

	public function getStructureAttribute()
	{
		return $this->getBlockStructure();
	}


	public function setSlugAttribute($slug)
	{
		if(!$this->route)
		{
			$this->setRelation('route',new Route);
		}

		return ($this->route->slug = $slug);

	}

	public function setRootAttribute($root)
	{
		if(!$this->route)
		{
			$this->setRelation('route',new Route);
		}

		return ($this->route->root = $root);
	}

	public function setParentAttribute($parent)
	{
		if(!$this->route)
		{
			$this->setRelation('route',new Route);
		}

		return ($this->route->parent = Route::where('page_id', $parent)->first());
	}

	public function getParentAttribute()
	{
		return $this->route->parent;
	}

	public function ancestors()
	{
		return $this->route->ancestors()->get();
	}

	public function descendants()
	{
		$hierarchy = $this
				->route
				->descendants()
				->with('page')
				->limitDepth(3)
				->get()
				->toHierarchy()
				->toArray();

		return array_values($hierarchy);
	}

	public function descendantsAndSelf()
	{
		$hierarchy = $this
				->route
				->descendantsAndSelf()
				->with('page')
				->limitDepth(3)
				->get()
				->toHierarchy()
				->toArray();

		return array_values($hierarchy)[0];
	}

	public static function findByPath($path)
	{
		$route = Route::where('path', '=', $path)->with('page', 'page.blocks')->first();

		if(isset($route))
		{
			// don't requery
			$page = $route->page;
			$page->setRelation('route', $route);
			return $page;
		}

		return null;
	}

	public function scopeSites($query)
	{
		return $query->where('is_site', 1)->with('route');
	}

	// To me: clean up crazy code
	public function saveBlocks($block_array)
	{
		$block_array = $this->flattenBlockStructure($block_array);

		$blockModels = $this->blocks->keyBy('id');

		foreach($block_array as $block)
		{
			if(isset($block['id']))
			{
				$b = $blockModels->get( (int)$block['id']);
				if($b)
				{
					// get real parent id
					$b->parent_block = ($b->parent_block != 0) ? $block_array[$block['parent_block']]['id'] : 0;
					// Save changes
					$b->fill($block)->save();
					// update block array so children can figure out their real parents
					$block_array[$block['blockorderid']]['id'] = $b->id;
				}
				// skipped blocks owned by another page
			}
			else
			{
				$b = new Block($block);

				$b->parent_block = ($b->parent_block != 0) ? $block_array[$block['parent_block']]['id'] : 0;
				$this->blocks()->save($b);

				$block_array[$block['blockorderid']]['id'] = $b->id;
			}
		}

		return true;
	}


	public function scopePageHierarchy($query)
	{
		// return $query->toHierarchy()->;
	}

	protected function flattenBlockStructure($block_array, $parent = 0)
	{
		$blocks = [];

		foreach($block_array as $c => $block)
		{
			$block['parent_block'] = $parent;
			$block['order'] = $c;

			// Ensure parent comes before child in array
			$blocks[$block['blockorderid']] = $block;

			// recurse
			if(isset($block['children']))
			{
				$blocks = $blocks + $this->flattenBlockStructure($block['children'], $block['blockorderid']);
			}
			unset($blocks[$block['blockorderid']]['children']);
		}

		return $blocks;
	}

	public function getBlockStructure()
	{
		// currently only handles one layer of nesting - can refactor to a nice recursive method at some point
		$blocks = $this->blocks->toArray();
		$structure = [];
		$count = 0;
		foreach($blocks as $block)
		{
			$block['blockorderid'] = $count++;
			if($block['parent_block'] === 0)
			{
				$structure[$block['id']] = $block;
			}
			else
			{
				if(!isset($structure[$block['parent_block']]['children']))
				{
					$structure[$block['parent_block']]['children'] = [];
				}
				$structure[$block['parent_block']]['children'][] = $block;
			}
		}


		return array_merge($structure, []);
	}
}
