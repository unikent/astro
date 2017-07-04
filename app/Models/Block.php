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

	protected $definition = null;


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
	public function loadDefinition()
	{
		$file = BlockDefinition::locateDefinition($this->definition_name, $this->definition_version);
		$definition = BlockDefinition::fromDefinitionFile($file);

		$this->definition = $definition;
	}

	/**
	 * Returns the definition, loading from disk if necessary.
	 * @return BlockDefinition
	 */
	public function getDefinition(){
		if(!$this->definition){
			$this->loadDefinition();
		}

		return $this->definition;
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
		static::where('page_id', '=', $page_id)
			->where('region_name', '=', $region)
			->delete();
	}

	/**
	 * Each block can have mutliple media items associated with it.
	 */
	public function media()
	{
		return $this->belongsToMany(Media::class, 'media_blocks')->withPivot('block_associated_field');
	}

	public function embedMedia()
	{
		$block = $this->media->each(function($mediaItem) {
			$field = $mediaItem->pivot->block_associated_field;
			$fields = $this->fields;
			unset($mediaItem->pivot);

			$this->associated_field = $field;

			$this->fields = array_set($fields, $field, $mediaItem);
		});
	}

}
