<?php

namespace App\Models;

use DB;
use Exception;
use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Exceptions\UnpublishedParentException;
use App\Models\Definitions\Layout as LayoutDefinition;
use League\Fractal\TransformerAbstract as FractalTransformer;

class PageContent extends Model
{
	use Tracked, SoftDeletes;

	protected $table = 'page_content';

	protected $fillable = [
		'title',
		'options',
		'layout_name',
		'layout_version',
        'site_id'
	];

	protected $casts = [
        'options' => 'json',
	];

	protected $layoutDefinition = null;

	public function routes()
	{
		return $this->hasMany(Page::class, 'draft_page_content_id');
	}

	public function activeRoute()
	{
	    throw new Exception('Not implemented');
		return $this->hasOne(Page::class, 'published_revision_id');
	}

	public function draftRoute()
	{
		return $this->hasOne(Page::class, 'draft_page_content_id');
	}


	public function blocks()
	{
		return $this->hasMany(Block::class, 'page_content_id');
	}

	public function published()
	{
        throw new Exception('Not implemented');
		//return $this->hasOne(Revision::class, 'published_revision_id')
	}

	public function history()
	{
		return $this->hasMany(Revision::class, 'page_content_id');
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
			throw new Exception('You cannot publish a pagecontent with unsaved changes.');
		}

		// Ensure that the draft does not have unpublished ancestors
		if($this->hasUnpublishedAncestors() ) {
			throw new UnpublishedParentException('Page cannot be published: it has unpublished ancestors.');
		}

		DB::beginTransaction();

		try {
			// If there is a draft route, publish it
			if($this->draftRoute) $this->draftRoute->makeActive();

			// Ensure that important relations are populated and up-to-date
			$this->load([ 'blocks', 'draftRoute', 'activeRoute' ]);

			// Create our Revision, bake it with Fractal
			$published = new Revision;
			$published->page_content_id = $this->getKey();
			$published->bake = fractal($this, $transformer)->parseIncludes([ 'blocks', 'activeRoute' ])->toJson();
			$published->save();

			DB::commit();
		} catch(Exception $e){
			DB::rollback();
			throw $e;
		}

		$this->load([ 'draftRoute', 'activeRoute' ]);
	}

    public function hasUnpublishedAncestors()
    {
        return true;
    }

	/**
	 * Restores a PageContent from a Revision instance. All Block instances are
	 * replaced with those defined by the bake. Routes remain unaltered.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function revert(Revision $published){
		if($this->getKey() !== $published->page_content_id){
			throw new Exception('Revision must be related to this PageContent');
		}

		if($this->isDirty()){
			throw new Exception('A PageContent must be in a clean state in order to revert.');
		}

		DB::beginTransaction();

		try {
			$baked = json_decode($published->bake, TRUE);
			$baked = $baked['data'];

			// Restore the Page object
			$this->fill($baked);
			$this->save();

			// Restore the Block instances
			$this->blocks()->delete();

			if(isset($baked['blocks'])){
				foreach($baked['blocks'] as $region => $blocks){
					foreach($blocks as $data){
						$block = new Block;
						$block->page_content_id = $this->getKey();
						$block->fill($data);
						$block->save();
					}
				}
			}

			DB::commit();
		} catch(Exception $e){
			DB::rollback();
			throw $e;
		}

		$this->load([ 'blocks', 'routes', 'draftRoute', 'activeRoute' ]);
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
