<?php
namespace App\Models\Definitions;

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
		foreach($this->regions as $name){
			$path = Region::locateDefinition($name);

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
