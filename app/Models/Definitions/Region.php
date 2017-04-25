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

    /**
     * Convert the model instance to an array.
     *
     * This is the same implementation as Illuminate\Database\Eloquent\Model.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = parent::toArray();

        if(!$this->blockDefinitions->isEmpty()){
	        $attributes['blockDefinitions'] = $this->blockDefinitions->toArray();
        }

        return $attributes;
    }

}
