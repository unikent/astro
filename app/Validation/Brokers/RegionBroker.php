<?php
namespace App\Validation\Brokers;

use App\Models\Definitions\Region as RegionDefinition;

class RegionBroker extends DefinitionBroker
{

	public function __construct(RegionDefinition $definition)
	{
		$this->definition = $definition;
	}

	/**
	 * Creates an array of validation rules based on Section block-constraints
	 * NOTE: this return value is not a ruleset but an array of rules to be assembled into 
	 * a laravel validation ruleset elsewhere
	 * 
	 * @param \App\Models\Definition\Region $region
	 * @param String $section_name
	 * @return Array [
	 *				"allowedBlocks" => 'rules for which blocks are allowed',
	 *				"blocksRequired" => 'rules to determin if blocks are required'
	 *				"blockLimits" => 'rules for the amount of blocks permitted'
	 *			]
	 */
	public function getSectionConstraintRules($section_name)
	{
		$region_definition = $this->definition;
				
		// defining the sets of rules to be returned
		$rules = [
			'allowedBlocks' => [],
			'blocksRequired' => [],
			'blockLimits' => []
		];

		foreach ($region_definition->sections as $section_definition) {
			if ($section_definition['name'] === $section_name) {

				if (isset($section_definition['allowedBlocks'])) {
					$rules['allowedBlocks'] = [
						'definition_name' => [
							'inVersioned:{version},' . implode(',', $section_definition['allowedBlocks']),
						]
					];
				}

				if (isset($section_definition['optional']) && $section_definition['optional'] ===false) {
					$rules['blocksRequired'] = [ 
						'blocks' => ['required']
					];
				}

				$block_limits = [];

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
					'blocks' => $block_limits
				];
                
                break;
			}
		}
		
		return $rules;
	}

}
