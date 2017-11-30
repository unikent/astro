<?php
namespace App\Models\Definitions;

use Illuminate\Support\Collection;

class SiteDefinition extends BaseDefinition
{

	public static $defDir = 'sites';

	protected $casts = [
        'allowedLayouts' => 'array',
	];

	/**
	 * Loads the Region definitions from disk and populates $regionDefinitions.
	 *
	 * @return void
	 */
	public function loadRegionDefinitions(){
		foreach($this->regions as $region_id){
			$path = Region::locateDefinition($region_id);
			if(!is_null($path)){
				$region = Region::fromDefinitionFile($path);
				$this->regionDefinitions->push($region);
			}
		}
	}

	/**
	 * Returns the regionDefinitions Collection, populating it from disk if necessary.
	 *
	 * @return Collection
	 */
	public function getRegionDefinitions(){
		if($this->regionDefinitions->isEmpty() && count($this->regions)){
			$this->loadRegionDefinitions();
		}

		return $this->regionDefinitions;
	}
}
