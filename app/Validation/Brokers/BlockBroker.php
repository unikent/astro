<?php
namespace App\Validation\Brokers;

use Astro\API\Models\Definitions\Block as BlockDefinition;
use Astro\API\Models\Definitions\Region as RegionDefinition;

class BlockBroker extends DefinitionBroker
{

	/**
	 * Loads the rules from the field definitions, runs them through the Transformer.
	 *
	 * @return Array
	 */
	public function getRules()
	{
		$rules = [];

		foreach($this->definition->fields as $field)
		{
			$fieldName = $field['name'];

			if(isset($field['validation']))
			{
				$rules[$fieldName] = $field['validation'];
			}

			if($nestedType = $this->nestedRuleType($field))
			{
				// If this field has nested fields, validate as array
				$rules[$fieldName][] = 'array';
				$this->getNestedRules($rules, $nestedType, $field);
			}
		}

		return $this->transformRules($rules);
	}

	/**
	 * Checks if a field has nested fields and return what "type" it is.
	 *
	 * @return boolean
	 */
	protected function nestedRuleType($field)
	{
		if($field['type'] === 'group' || (isset($field['nested']) && $field['nested']))
		{
			return 'nested';
		}
		else if($field['type'] === 'collection')
		{
			return 'collection';
		}

		return false;
	}

	/**
	 * Gets nested rules for a field and adds them to the passed in rules array.
	 *
	 * @param      Array  $rules  The rules array.
	 * @param      Array  $field  The field to check
	 */
	protected function getNestedRules(&$rules, $type, $field)
	{
		$inArray = $type === 'collection';

		if(isset($field['fields']) && is_array($field['fields']))
		{
			foreach($field['fields'] as $nested)
			{
				if(isset($nested['validation']))
				{
					$nestedName = sprintf(
						$inArray ? '%s.*.%s' : '%s.%s',
						$field['name'],
						$nested['name']
					);

					$rules[$nestedName] = $nested['validation'];
				}
			}
		}
	}

	/**
	 * Creates a validation rule based on Region block-constraints
	 * @Todo is this needed?
	 *
	 * @param \Astro\API\Models\Definition\Region $region
	 * @return Array
	 */
	public function getRegionConstraintRules(RegionDefinition $region)
	{
		return [
			'definition_name' => [
				'in:' . implode(',', $region->blocks),
			]
		];
	}

}
