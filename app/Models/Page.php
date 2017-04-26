<?php

namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;
use App\Models\Definitions\Layout as LayoutDefinition;

class Page extends Model
{
	use Tracked;

	protected $fillable = [
		'title',
		'options',
		'layout_name',
		'layout_version',
	];

	protected $casts = [
        'options' => 'json',
        'is_site' => 'boolean',
        'is_published' => 'boolean',
	];

	protected $layoutDefinition = null;

	public function canonical()
	{
		return $this->hasOne(Route::class, 'page_id')->where('is_canonical', '=', true);
	}

	public function routes()
	{
		return $this->hasMany(Route::class, 'page_id');
	}

	public function blocks()
	{
		return $this->hasMany(Block::class, 'page_id');
	}


	/**
	 * Loads the Layout definition, optionally including Regions
	 *
	 * @param boolean $includeRegions
	 * @return void
	 */
	public function loadLayoutDefinition($includeRegions = false)
	{
		$file = LayoutDefinition::locateDefinition($this->layout_name, $this->layout_version);
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
	 * Scopes query to return Pages where 'is_site' is true
	 *
	 * @param  $query
	 * @return Collection
	 */
	public function scopeSites($query)
	{
		return $query->where('is_site', 1);
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


}
