<?php
namespace App\Models\Definitions;

use Illuminate\Support\Collection;

class Region extends BaseDefinition
{

	public static $defDir = 'regions';

	protected $casts = [
        'blocks' => 'array',
	];

	protected $blockDefinitions;

	public function __construct(){
		$this->blockDefinitions = new Collection;
	}

	/**
	 * Loads the block definitions from disk and populates $regionDefinitions.
	 *
	 * @return void
	 */
	public function loadBlockDefinitions(){
		foreach($this->blocks as $name){
			$path = Block::locateDefinition($name);

			if(!is_null($path)){
				$block = Block::fromDefinitionFile($path);
				$this->blockDefinitions->push($block);
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
