<?php
namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;
use App\Models\Definitions\Block as BlockDefinition;

class Block extends Model
{
	use Tracked;

	public $fillable = [
		'definition_name',
		'definition_version',
		'region_name',
		'fields',
		'order',
	];

	protected $casts = [
        'fields' => 'json',
	];

	protected $blockDefinition = null;


    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct($attributes = []){
        parent::__construct($attributes);
        $this->fields = $this->fields ?: [];
    }


	/**
	 * Loads the Block definition
	 * @return void
	 */
	public function loadBlockDefinition()
	{
		$file = BlockDefinition::locateDefinition($this->definition_name, $this->definition_version);
		$definition = BlockDefinition::fromDefinitionFile($file);

		$this->blockDefinition = $definition;
	}

	/**
	 * Returns the blockDefinition, loading from disk if necessary.
	 * @return BlockDefinition
	 */
	public function getBlockDefinition(){
		if(!$this->blockDefinition){
			$this->loadBlockDefinition();
		}

		return $this->blockDefinition;
	}


	/**
	 * Deletes all blocks for a given Page and Region.
	 *
	 * @param  Page|int $page_or_id
	 * @param  string $region
	 * @return void
	 */
	public static function deleteForPageRegion($page_or_id, $region)
	{
		$page_id = is_numeric($page_or_id) ? $page_or_id : $page_or_id->getKey();
		static::where('page_id', '=', $page_id)->where('region_name', '=', $region)->delete();
	}



    /**
     * Convert the model instance to an array.
     * If loaded, includes the $blockDefinition.
     *
     * @return array
     */
    public function toArray()
    {
    	$attributes = $this->attributesToArray();

    	if($this->blockDefinition){
    		$attributes['blockDefinition'] = $this->blockDefinition;
    	}

        return $attributes;
    }

}
