<?php
namespace App\Models\Definitions;

use Exception;
use App\Models\Page;
use App\Events\PageEvent;
use App\Helpers\CachedHttpClient;
use Illuminate\Support\Facades\Log;

// use Illuminate\Support\Facades\Redis;
// use Illuminate\Support\Facades\Cache;
// use GuzzleHttp\Client as GuzzleClient;

class Block extends BaseDefinition
{

	public static $defDir = 'blocks';

	protected $casts = [
		'fields' => 'array',
	];

	/**
	 * Get any dynamically generated attributes for this block
	 * @param array $block_data - Array of data with values for this block.
	 * @param string $section_name - The name of the section this block is in.
	 * @param string $region_name - The name of the region this block is in.
	 * @param array $page_data - The page data (as structured to be sent as json) that this block is part of.
	 * @return mixed Array of data.
	 */
	public function getDynamicAttributes($block_data, $section_name, $region_name, $page_data){ return []; }

	/**
	 * Provide dynamic routing.
	 * This must be implemented by a custom block class.
	 * @param $path - Path to route
	 * @param $block - Reference to the block data that is being returned as part of the page.
	 * @param $page - The Page object.
	 * @return bool
	 */
	public function route($path, &$block, $page) {return false;}

	/**
	 * Called whenever a page containing this block is changed, created, deleted, moved, etc
	 * @param PageEvent $page_event - The object with information about this event.
	 * @param array $block_data - The data defining this block
	 * @param string $region_name - Name of the region containing this block.
	 * @param int $section_index - Index within its region of the section containing this block.
	 * @param array $section_def - Definition of the section containing this block ( ['name' => ..., 'blocks' => [...] ] )
	 * @param int $block_index - The index of this block inside its section
	 */
	public function onPageStatusChange(PageEvent $page_event, array $block_data, $region_name, $section_index, $section_def, $block_index ) { }

	/**
	 * Are blocks of this type dynamic?
	 * @return mixed
	 */
	public function isDynamic()
	{
		return $this->dynamic;
	}

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
	 * Does the field definition provide a (explicit or implicit) default value, or do we need to investigate it further?
	 * @param array $field_definition [ 'name' => '...', 'type' => '...', 'default' => ???, etc]
	 * @return bool - True if we can get a default value for the field from the definition, false if eg. it is a nested field.
	 */
	public function hasDefaultValue($field_definition)
	{
		return array_key_exists('default', $field_definition) ||
				!in_array($field_definition['type'], ['collection', 'group']);
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
			if($this->hasDefaultValue($field)){
				$values[$field['name']] = array_key_exists('default', $field) ? $field['default'] : null;
			}
			elseif('collection' == $field['type']){
				$min = 0;
				foreach($field['validation'] as $rule){
					if( preg_match('/^min:([0-9]+)$/i', $rule, $match)) {
						$min = $match[1];
					}
				}
				$vals = [];
				for($i = 0; $i < $min; ++$i){
					$vals[] = $this->defaultFieldValues($field['fields']);
				}
				$values[$field['name']] = $vals;
			}
			elseif( isset($field['fields'])){
				$values[$field['name']] = $this->defaultFieldValues($field['fields']);
			}
		}
		return $values;
	}

	/**
	 * Decodes a JSON-blob of definition-data and returns
	 * a populated model instance.
	 *
	 * @param  string $json
	 * @return DefinitionContract
	 */
	public static function fromDefinition($json)
	{
		$definition = parent::fromDefinition($json);

		// get dynamic options
		$definition->fields = self::processDynamicFields($definition->fields);

		return $definition;
	}

	/**
	 * Traverse through all dynamic fields recursively,
	 * pulling in their dynamic options
	 *
	 * @param  array $fields
	 * @return array an array of fields with their dynamic options inserted
	 * @todo we we want to remove the dynamic options prop after replacement of options?
	 */
	public static function processDynamicFields($fields)
	{
		/*
		for loop block through each field in $definition and if there dynamic options then look those up
		and set the options
		*/
		if (0 != count($fields)) {
			foreach ($fields as &$field) {
				if (isset($field['dynamic_options'])) {
					if (isset(
						$field['dynamic_options']['url'],
						$field['dynamic_options']['label_field'],
						$field['dynamic_options']['value_field']
					)) {
						if (!isset($field['dynamic_options']['cache_time'])) {
							$field['dynamic_options']['cache_time'] =
								config('definitions.dynamic_options_cache_time');
						}

						$dynamicOptions = self::getDynamicOptions(
							$field['dynamic_options']['url'],
							$field['dynamic_options']['label_field'],
							$field['dynamic_options']['value_field'],
							$field['dynamic_options']['cache_time']
						);
						if ($dynamicOptions) {
							$field['options'] = $dynamicOptions;
						}
					}
				}
				if (isset($field['fields'])) {
					$field['fields'] = self::processDynamicFields($field['fields']);
				}
			}
		}
		return $fields;
	}

	/**
	 * getDynamicOptions
	 *
	 * calls an api, returns an array of key and values
	 *
	 * @param string $url - an api endpoint to query
	 * @param string $labelField
	 * @param string $valueField
	 * @param int $cacheTime - time in minutes to store newly fetched items in cache
	 * @param object $cachedHttpClient - a http client with a get method
	 * @return array assoc array of keys and their values
	 */
	public static function getDynamicOptions(
		$url,
		$labelField,
		$valueField,
		$cacheTime,
		$cachedHttpClient = null
	) {
		// default to no options
		$options = [];

		if (null !== $cachedHttpClient) {
			$cachedHttpClient = new CachedHttpClient();
		}

		// get options from cached api call
		$result = $cachedHttpClient->get($url, $cacheTime);

		if ($result) {
			try {
				$items = json_decode($result, true);
				foreach ($items as $item) {
					if (isset($item[$valueField], $item[$labelField])) {
						$options[] = [
							'value' => $item[$valueField],
							'label' => $item[$labelField]
						];
					}
				}
			} catch (\Exception $e) {
				Log::error("Failed to decode API response for dynamic options $url");
				throw $e;
			}
		}

		return $options;
	}
}
