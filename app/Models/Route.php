<?php
namespace App\Models;
use \Baum\Node as Model;


class Route extends Model
{
	public $timestamps = false;

	// None persistant attributes
	public $root = null;
	public $parent = null;

	protected $appends = ['title'];
	protected $hidden = ['page','lft','rgt'];

	public function getTitleAttribute(){
		return $this->page->title;
	}

	public function page()
	{
		return $this->hasOne('App\Models\Page', 'id');
	}

	public function save(array $options = [])
	{
		// If new MUST either be a root node, or have a parent specified
		if(!$this->exists && !($this->parent || $this->root))
		{
			throw new \Exception("Route must have either a parent, or be type root.");
		}

		// Generate path
		$this->path = $this->generatePath($this->parent);

		$success = parent::save($options);

		if($this->parent){
			$this->makeChildOf($this->parent);
		}
		if($this->root){
			$this->makeRoot();
		}

		return $success;
	}

	protected function generatePath($parent = null)
	{
		if($this->root) return '/';

		$chain = ($this->parent) ? $this->parent->ancestorsAndSelf(['slug'])->get() : $this->ancestors(['slug'])->get();

		$path_prefix = '';
		foreach($chain as $ancestor)
		{
			// skip root node
			if(empty($ancestor->slug)) continue;

			$path_prefix .= "/{$ancestor->slug}";
		}

		return $path_prefix.'/'.$this->slug;
	}
}
