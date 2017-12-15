<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Feature\Traits\CreatesFeatureFixtures;
use Tests\Feature\Traits\ExtractsPageAttributesFromPageJson;
use Tests\Feature\Traits\LoadsFixtureData;
use Tests\Feature\Traits\MakesAssertionsAboutSites;
use Tests\Feature\Traits\MakesAssertionsAboutPages;
use Tests\Feature\Traits\ValidatesJsonSchema;

/**
 * Feature tests for the add page api endpoint.
 * See the traits used for additional information about how these tests work.
 * @package Tests\Feature
 */
class CreateSiteTest extends TestCase
{
	use CreatesFeatureFixtures,
		ExtractsPageAttributesFromPageJson,
		MakesAssertionsAboutSites,
		MakesAssertionsAboutPages,
		ValidatesJsonSchema,
		LoadsFixtureData;

	/**
	 * Provides valid data for creating a site with.
	 * @return array
	 */
	public function validSiteDataProvider()
	{
		return $this->getValidFixtureData('CreateSite');
	}

	/**
	 * @test
	 * @group api
	 * @dataProvider validSiteDataProvider
	 */
	public function createSite_withValidData_createsASiteWithPagesBasedOnSiteDefinition($data)
	{
		$response = $this->json(
			'POST',
			'/api/v1/sites',
			$data,
			['Authorization' => 'Bearer ' . $this->admin->api_token]
		);
		$response->assertStatus(201);
	}

	/**
	 * @group api
	 * @test
	 */
	public function createSite_withoutAdminPrivileges_failsWith401()
	{

	}

	/**
	 * Data provider providing data that is invalid to create a site.
	 * @return array
	 */
	public function invalidSiteDataProvider()
	{
		return [];
	}

	/**
	 * @group api
	 * @test
	 * @dataProvider invalidSiteDataProvider
	 */
	public function createSite_withInvalidData_failsWith422($data)
	{

	}
}