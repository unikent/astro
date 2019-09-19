<?php

namespace Tests\Feature\Traits;

/**
 * Trait to extract page attributes from page json -> array data structures
 * to simplify the actual tests.
 * The $data parameter passed to most of the methods should represent actual page data, and not include the top-level
 * ['data' => ...] attribute found is most API responses.
 * @package Tests\Feature\Traits
 */
trait ExtractsPageAttributesFromPageJson
{
	/**
	 * Get the page title if it exists.
	 * @param array $data Array of key => value pairs representing the json data for a page returned by the api.
	 * @return string|bool - The page title or false if the title is not present in the data.
	 */
	public function getPageTitle($data)
	{
		return $this->getPageAttribute('title', $data);
	}

	/**
	 * Get the page slug if it exists.
	 * @param array $data Array of key => value pairs representing the json data for a page returned by the api.
	 * @return string|bool - The page slug or false if the slug is not present in the data.
	 */
	public function getPageSlug($data)
	{
		return $this->getPageAttribute('slug', $data);
	}

	/**
	 * Gets
	 * @param $data
	 * @return bool
	 */
	public function getPageId($data)
	{
		return $this->getPageAttribute('id', $data);
	}

	/**
	 * Get any key if it exists in the array of data.
	 * @param $key
	 * @param $data
	 * @return bool
	 */
	public function getPageAttribute($key, $data)
	{
		return array_key_exists($key, $data) ? $data[$key] : false;
	}
}