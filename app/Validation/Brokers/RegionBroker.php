<?php
namespace App\Validation\Brokers;

use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definitions\Region as RegionDefinition;

class BlockBroker extends DefinitionBroker
{

	/**
	 * Creates an array of validation rules based on Section block-constraints
	 * NOTE: this return value is not a ruleset but an array of rules to be assembled into 
	 * a laravel validation ruleset elsewhere
	 * 
	 * @param \App\Models\Definition\Region $region
	 * @param String $section_name
	 * @return Array [
	 *				"allowedBlocks" => 'rules for which blocks are allowed',
	 *				"blockLimits" => 'rules for the amount ob blocks permitted'
	 *			]
	 */
	public function getSectionConstraintRules($section_name)
	{
		$region_definition = $this->definition;
		foreach ($region_definition->sections as $section_definition) {
			if ($section_definition['name'] === $section_name) {
				
				$rules = [];

				if (isset($section_definition['allowedBlocks'])) {
					$rules['allowedBlocks'] = [
						'definition_name' => [
							'in:' . implode(',', $section_definition['allowedBlocks']),
						]
					];
				}

				$block_limits = [];

				if (isset($section_definition['optional']) && $section_definition['optional'] ===false) {
					$block_limits[] = 'required';
				}

				if (isset($section_definition['size'])) {
					$block_limits[] = 'size:'.$section_definition['size'];
				}
				else{

					if (isset($section_definition['min'])) {
						$block_limits[] = 'min:'.$section_definition['min'];
					}

					if (isset($section_definition['max'])) {
						$block_limits[] = 'max:'.$section_definition['max'];
					}
				}

				$rules['blockLimits'] =  [
					'blocks' => $block_limits;
				];

				return $rules;
			}
		}
		
		return [];
	}

}
