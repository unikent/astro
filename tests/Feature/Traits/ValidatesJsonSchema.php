<?php

namespace Tests\Feature\Traits;

/**
 * Validates json OBJECTS against json schemas.
 * @package Tests\Feature\Traits
 */
trait ValidatesJsonSchema
{
	// cache objects if possible
	public static $dereferencer = null;
	public static $schemaCache = [];
	public $jsonValidationErrors = null;

	/**
	 * @param object $data Json data to validate - must be in object format, not array format.
	 * @param string $schema_filename - the filename of the schema to validate against.
	 * @return bool - True if the data passes validation, otherwise false.
	 */
	public function validateJsonSchema($data, $schema_filename)
	{
		$schema = $this->getJsonSchema($schema_filename);
		$validator = new League\JsonGuard\Validator($data, $schema);
		if($validator->passes()){
			$this->jsonValidationErrors = false;
			return true;
		}
		else {
			$this->jsonValidationErrors = $validator->errors();
			return false;
		}
	}

	/**
	 * Gets any errors from the last validation check.
	 * @return array|false - The errors or false if no errors.
	 */
	public function getJsonValidationErrors()
	{
		return $this->jsonValidationErrors;
	}

	/**
	 * Loads a dereferenced json schema from disk.
	 * @param string $schema_filename - The URI of the schema to use.
	 * @return string - the dereferenced json schema.
	 */
	private function getJsonSchema($schema_filename)
	{
		if(!array_key_exists($schema_filename, static::$schemaCache)) {
			static::$schemaCache[$schema_filename] = $this->dereferenceJsonSchema($schema_filename);
		}
		return static::$schemaCache[$schema_filename];
	}

	/**
	 * Dereference a json schema.
	 * @param string $schema - The URL to the schema.
	 * @return string - The dereferenced json schema.
	 */
	private function dereferenceJsonSchema($schema)
	{
		if(!static::$dereferencer){
			static::$dereferencer = League\JsonReference\Dereferencer::draft4();
		}
		return static::$dereferencer->dereference($schema);
	}
}