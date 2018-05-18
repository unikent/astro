<?php

namespace Tests\Feature\Traits;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema;

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
	public $schema_path = __DIR__ . '/../../Support/Schemas';

	/**
	 * @param object $data Json data to validate - must be in object format, not array format.
	 * @param string $schema_filename - the filename of the schema to validate against.
	 * @return bool - True if the data passes validation, otherwise false.
	 */
	public function assertValidJsonSchema($data, $schema_filename)
	{
		try {
			if (!isset(self::$schemaCache[$schema_filename])) {
				$schema_file = $this->schema_path . '/' . $schema_filename;
				$json = json_decode(file_get_contents($schema_file),false);
				self::$schemaCache[$schema_filename] = Schema::import($json);
			}
			self::$schemaCache[$schema_filename]->in($data);
		} catch (InvalidValue $e) {
			$this->fail(print_r($e->inspect(), true));
		}
	}
}