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
 * Feature tests for the create site api endpoint.
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
		// ensure site with this host and path does not already exist
		$this->assertFalse($this->siteExistsWithHostAndPath($data['host'], $data['path']));
		$response = $this->json(
			'POST',
			'/api/v1/sites',
			$data,
			['Authorization' => 'Bearer ' . $this->admin->api_token]
		);
		$response->assertStatus(201);
		$this->assertTrue($this->siteExistsWithHostAndPath($data['host'], $data['path']));
		$json = json_decode($response->getContent(), true);
	}

	/**
	 * Data provider for users who should not be able to create sites
	 * @return array
	 */
	public function usersWhoCannotCreateSitesProvider()
	{
		return $this->packArrayForProvider(['randomer','contributor','editor', 'owner']);
	}

	/**
	 * @group api
	 * @test
	 * @dataProvider usersWhoCannotCreateSitesProvider
	 */
	public function createSite_withoutPrivileges_failsWith403($user)
	{
		foreach($this->getValidFixtureData('CreateSite') as $fixture => $payload) {
			$response = $this->json(
				'POST',
				'/api/v1/sites',
				$payload,
				['Authorization' => 'Bearer ' . $this->$user->api_token]
			);
			$response->assertStatus(403);
		}
	}

	/**
	 * Data provider providing data that is invalid to create a site.
	 * @return array
	 */
	public function invalidSiteDataProvider()
	{
		return $this->getInvalidFixtureData('CreateSite');
	}

	/**
	 * @group api
	 * @test
	 * @dataProvider invalidSiteDataProvider
	 */
	public function createSite_withInvalidData_failsWith422($data)
	{
		$response = $this->json(
			'POST',
			'/api/v1/sites',
			$data,
			['Authorization' => 'Bearer ' . $this->admin->api_token]
		);
		$response->assertStatus(422);
		$json = json_decode($response->getContent(), true);
		print_r($json);
	}
}