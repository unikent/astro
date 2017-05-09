<?php
namespace App\Validation\DefinitionValidators;

use Illuminate\Validation\ValidationException;
use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definition\BaseDefinition as Definition;
use Illuminate\Validation\Validator as LaravelValidator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

abstract class DefinitionValidator {


	protected $definition;


	public function __construct(BlockDefinition $definition)
	{
		$this->definition = $definition;
	}


	/**
	 * Loads the rules from the field definitions, runs them through the Transformer.
	 *
	 * @return Array
	 */
	public function getRules()
	{
		$this->transformRules([]);
	}


	/**
	 * Transforms the validation syntax found in definitions, to the syntax
	 * used by Laravel.
	 *
	 * @param Array $rules
	 * @return Array
	 */
	protected function transformRules($rules)
	{
		$transformed = [];

		foreach($rules as $field => $ruleset){
			foreach($ruleset as &$rule){
				$rule = explode(':', $rule);

				switch($rule[0]){
					case 'required':
						$transformed[$field][] = 'present';
						$transformed[$field][] = 'required';
						break;

					case 'min_length':
						$transformed[$field][] = 'string';
						$transformed[$field][] = sprintf('min:%s', $rule[1]);
						break;

					case 'max_length':
						$transformed[$field][] = 'string';
						$transformed[$field][] = sprintf('max:%s', $rule[1]);
						break;

					case 'min_value':
						$transformed[$field][] = 'integer';
						$transformed[$field][] = sprintf('min:%s', $rule[1]);
						break;

					case 'max_value':
						$transformed[$field][] = 'integer';
						$transformed[$field][] = sprintf('max:%s', $rule[1]);
						break;

					default:
						$transformed[$field][] = isset($rule[1]) ? sprintf('%s:%s', $rule[0], $rule[1]) : $rule[0];
						break;
				}
			}

			$transformed[$field] = array_unique($transformed[$field]);
		}

		return $transformed;
	}


	/**
	 * Returns a new Validator instance
	 *
	 * @param Array $data
	 * @param Array $messages
	 * @return \Illuminate\Validation\Validator
	 */
	public function getValidator(Array $data = [], Array $messages = [])
	{
		return ValidatorFacade::make($data, $this->getRules(), $messages);
	}


	/**
	 * Runs 'passes()' on the Validator instance.
	 *
     * @return void
     *
	 * @throws \Illuminate\Validation\ValidationException;
	 */
	public function validate()
	{
		$validator = $this->getValidator();

		if(!$validator->passes()){
        	throw new ValidationException($validator);
		}
	}

}
