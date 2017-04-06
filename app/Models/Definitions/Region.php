<?php
namespace App\Models\Definitions;

use Illuminate\Support\Collection;

class Region extends BaseDefinition
{

    protected static $defDir = 'regions';

	protected $casts = [
        'blocks' => 'array',
	];

	public function __construct(){
		$this->blockDefinitions = new Collection;
	}

	/**
	 * Loads the block definitions from disk and populates $regionDefinitions.
	 *
	 * @return void
	 */
	protected function loadBlockDefinitions(){
		foreach($this->blocks as $name){
			$path = Block::locateDefinition($name);

			if(!is_null($path)){
				$region = Block::fromDefinitionFile($path);
				$this->blockDefinitions->push($region);
			}
		}
	}

	/**
	 * Returns the blockDefinitions Collection, populating it from disk if necessary.
	 *
	 * @return Collection
	 */
	public function getBlockDefinitions(){
		if($this->blockDefinitions->isEmpty() && count($this->blocks)){
			$this->loadBlockDefinitions();
		}

		return $this->blockDefinitions;
	}

}
