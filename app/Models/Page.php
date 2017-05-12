<?php

namespace App\Models;

use DB;
use Exception;
use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;
use App\Models\Definitions\Layout as LayoutDefinition;
use League\Fractal\TransformerAbstract as FractalTransformer;

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

	public function published()
	{
		return $this->hasOne(PublishedPage::class, 'page_id')->latest()->limit(1);
	}

	public function history()
	{
		return $this->hasMany(PublishedPage::class, 'page_id');
	}


	/**
	 * Creates a PublishedPage by baking the Page to JSON with Fractal.
	 * If the Page is dirty, an exception will be thrown.
	 *
	 * @param \League\Fractal\TransformerAbstract $transformer
	 * @return void
	 * @throws Exception
	 */
	public function publish(FractalTransformer $transformer)
	{
		if($this->isDirty()){
			throw new Exception('You cannot publish a page with unsaved changes.');
		}

		// Ensure that Blocks/Canonical are both loaded and fresh
		$this->load([ 'blocks', 'canonical' ]);

		DB::beginTransaction();

		try {
			// Create our PublishedPage, bake it with Fractal
			$published = new PublishedPage;
			$published->page_id = $this->getKey();
			$published->bake = fractal($this, $transformer)->parseIncludes([ 'blocks', 'canonical' ])->toJson();
			$published->save();

			// And update any inactive Routes
			$route = $this->routes()->active(false)->limit(1)->first();

			if($route){
				$route->makeActive();
				$route->makeCanonical();
			}

			DB::commit();
		} catch(Exception $e){
			DB::rollback();
			throw $e;
		}
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
	 * Deletes all blocks in the given Region
	 *
	 * @param  string $region
	 * @return void
	 */
	public function clearRegion($region){
		Block::deleteForPageRegion($this, $region);
	}

}
