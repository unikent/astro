<?php

namespace Tests\Feature\Traits;

/**
 * Scans a directory for files matching a pattern in the form {somename}_(invalid|valid).json each of which contains json data
 * that is either valid or invalid for use with api commands.
 * Data is loaded into an array suitable for use with phpunit data providers, keyed on the fixture filename.
 * @package Tests\Feature\Traits
 */
trait LoadsFixtureData
{
	static $fixtureCache = [];

	/**
	 * Gets the path where the fixture test data is stored.
	 * @return string - The path to the fixture data. Defaults to tests/Support/Fixtures/featuredata but can be overridden
	 * if the class using the trait defines a $fixturePath property.
	 */
	public function getFixturePath()
	{
		return !empty($this->fixturePath) ? $this->fixturePath : dirname(__FILE__) . '/../../Support/Fixtures/featuredata';
	}

	/**
	 * Finds fixture data in the fixture path with filenames matching the glob pattern in $match.
	 * Caches lookups to avoid too much disk scanning during tests. Possibly pointless as I think phpunit may just run these once before tests.
	 * @param string $match - Filename pattern for glob to match.
	 * @return array - Fixture data in array in form ['filename-without-dot-json' => data, ... ]
	 */
	public function getFixtureData($match)
	{
		if(!array_key_exists($match, static::$fixtureCache)) {
			$results = [];
			$pattern = $this->getFixturePath() . '/' . $match . '.json';
			foreach (glob($pattern) as $filename) {
				$data = json_decode($this->stripComments(file_get_contents($filename)), true);
				$id = preg_replace('/^.*?([a-z0-9_-]+)\.json$/i', '$1', $filename);
				$results[$id] = $data;
			}
			static::$fixtureCache[$match] = $results;
		}
		return static::$fixtureCache[$match];
	}

	public function stripComments($input)
	{
		return preg_replace('/^#.*$\r?\n?/m', '', preg_replace('/\/\*.*?\*\//s', '', $input));
	}

	/**
	 * Gets fixture data for testing a command matching the valid filename pattern.
	 * @param string $command - The command whose fixture data to load, e.g. CreateSite
	 * @return array - Fixture data in array in form ['filename-without-dot-json' => data, ... ]
	 */
	public function getValidFixtureData($command)
	{
		$data = $this->getFixtureData(strtolower(str_replace(' ', '', $command)). '_valid*');
		return $data;
	}

	/**
	 * Gets fixture data for testing a command matching the invalid filename pattern.
	 * @param string $command - The command whose fixture data to load, e.g. CreateSite
	 * @return array - Fixture data in array in form ['filename-without-dot-json' => data, ... ]
	 */
	public function getInvalidFixtureData($command)
	{
		return $this->getFixtureData(strtolower(str_replace(' ', '', $command)) . '_invalid*');
	}
}