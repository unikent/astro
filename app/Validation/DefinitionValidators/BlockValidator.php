<?php
namespace App\Validation\DefinitionValidators;

use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definitions\Region as RegionDefinition;

class BlockValidator extends DefinitionValidator {

	/**
	 * Loads the rules from the field definitions, runs them through the Transformer.
	 *
	 * @return Array
	 */
	public function getRules()
	{
		$rules = [];

		foreach($this->definition->fields as $definition){
			if(isset($definition['validation'])){
				$field = $definition['name'];
				$rules[$field] = $definition['validation'];
			}
		}

		return $this->transformRules($rules);
	}

	/**
	 * Creates a validation rule based on Region block-constraints
	 *
	 * @param \App\Models\Definition\Region $region
	 * @return Array
	 */
	public function getRegionConstraintRules(RegionDefinition $region = null)
	{
		return [
			'definition_name' => [
				'in:' . implode(',', $region->blocks),
			]
		];
	}

}
