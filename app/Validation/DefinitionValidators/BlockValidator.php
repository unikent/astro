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
	public function getRules(RegionDefinition $regionDefinition = null)
	{
		$rules = [];

		foreach($this->definition->fields as $definition){
			if(isset($definition['validation'])){
				$field = $definition['name'];
				$rules[$field] = $definition['validation'];
			}
		}

		$rules = $this->transformRules($rules);

		if(isset($regionDefinition)){
			$rules['definition_name'] = sprintf('in:%s', array_merge(',', $regionDefinition->blocks));
		}

		return $rules;
	}

}
