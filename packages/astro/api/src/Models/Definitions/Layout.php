<?php
namespace Astro\API\Models\Definitions;

use Illuminate\Support\Collection;

class Layout extends BaseDefinition
{

	public static $defDir = 'layouts';

	protected $regionDefinitions;

	protected $casts = [
        'regions' => 'array',
	];

	public function __construct(){
		$this->regionDefinitions = new Collection;
	}

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
	 * Get the data structure that defines the regions and sections for a page using this layout.
	 * @return array Array of [region-name => [section1, ...], ... ]
	 */
	public function getDataStructure()
	{
		$data = [];
		foreach($this->getRegionDefinitions() as $region_definition){
			$region_id = Region::idFromNameAndVersion($region_definition->name, $region_definition->version);
			$data[$region_id] = $region_definition->getDataStructure();
		}
		return $data;
	}

	/**
	 * Get the default page content (regions, sections and blocks) for this layout.
	 * @return array - [ region-name => [ [ 'name' => 'section-1-name', 'blocks' => [ ... block data ... ], ... ] ], ... ]
	 */
	public function getDefaultPageContent()
	{
		$regions = [];
		foreach($this->getRegionDefinitions() as $region_definition) {
			$regions[Region::idFromNameAndVersion($region_definition->name, $region_definition->version)] = $region_definition->getDefaultBlocks();
		}
		return $regions;
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
