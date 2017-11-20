<?php
namespace App\Models\Definitions;


class Block extends BaseDefinition
{

	public static $defDir = 'blocks';

	protected $casts = [
        'fields' => 'array',
	];

	/**
	 * Get default data and fields to represent an instance of this block.
	 *
	 * @param string $region_name - The name of the region containing this block.
	 * @param string $section_name - The name of the section containing this block.
	 * @return [ 'definition_name' => '...', 'definition_version' => '...', 'errors' => '...', 'fields' => '...']
	 */
	public function getDefaultData($region_name, $section_name)
	{
		$data = [
			'definition_name' => $this->name,
			'definition_version' => $this->version,
			'errors' => null,
			'region_name' => $region_name,
			'section_name' => $section_name,
			'fields' => $this->defaultFieldValues($this->fields)
		];
		return $data;
	}

	/**
	 * Get the default values for each field defined in $fields.
	 *
	 * @param array $fields - The 'fields' part of the block definition, or sub-fields.
	 * @return array - [field-names => values] for each field or group of fields defined with default values.
	 */
	public function defaultFieldValues($fields)
	{
		$values = [];
		foreach($fields as $field){
			if(isset($field['default'])){
				$values[$field['name']] = $field['default'];
			}
			elseif(isset($field['fields'])){ // group, collection, etc fields
				$values[$field['name']] = $this->defaultFieldValues($field['fields']);
			}
		}
		return $values;
	}
}
