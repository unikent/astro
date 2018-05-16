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
		return $this->combineForProvider($this->getValidFixtureData('CreateSite'));
	}

	/**
	 * @test
	 * @group api
	 * @dataProvider validSiteDataProvider
	 */
	public function createSite_withValidDataAndPermissions_createsASiteWithPagesBasedOnSiteDefinition($data)
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
	 * @group api
	 * @test
	 * @dataProvider validDataUnauthorizedUsersProvider
	 */
	public function createSite_withValidDataButInvalidPrivileges_failsWith403($payload, $user)
	{
		$response = $this->json(
			'POST',
			'/api/v1/sites',
			$payload,
			['Authorization' => 'Bearer ' . $this->$user->api_token]
		);
		$response->assertStatus(403);
		$this->assertFalse($this->siteExistsWithHostAndPath($payload['host'], $payload['path']));
	}

	/**
	 * Data provider with valid data but users who should not be able to create sites
	 * @return array [ [ $json_data, $username ], ... ]
	 */
	public function validDataUnauthorizedUsersProvider()
	{
		$users = ['randomer', 'contributor', 'editor', 'owner'];
		return $this->combineForProvider($this->getValidFixtureData('CreateSite'), array_combine($users, $users));
	}

	/**
	 * @group api
	 * @test
	 * @dataProvider unauthorizedUsersInvalidSiteDataProvider
	 */
	public function createSite_withInvalidData_failsWith403ForUnauthorizedUsers($payload, $user)
	{
		$response = $this->testCreateSiteWithInvalidData($payload, $user, 403);
		// @todo - test that response json is valid
		$json = json_decode($response->getContent(), true);
	}

	/**
	 * Data provider providing data that is invalid to create a site.
	 * @return array
	 */
	public function unauthorizedUsersInvalidSiteDataProvider()
	{
		$users = ['randomer', 'contributor', 'editor', 'owner'];
		return $this->combineForProvider($this->getInvalidFixtureData('CreateSite'), array_combine($users, $users));
	}

	/**
	 * @group api
	 * @test
	 * @dataProvider authorizedUsersInvalidSiteDataProvider
	 */
	public function createSite_withInvalidData_failsWith422ForAuthorizedUsers($payload, $user)
	{
		$response = $this->testCreateSiteWithInvalidData($payload, $user, 422);
		// @todo - test that response json is valid
		$json = json_decode($response->getContent(), true);
	}

	/**
	 * Data provider providing data that is invalid to create a site.
	 * @return array
	 */
	public function authorizedUsersInvalidSiteDataProvider()
	{
		$users = ['admin'];
		return $this->combineForProvider($this->getInvalidFixtureData('CreateSite'), array_combine($users, $users));
	}

	/**
	 * Reuse for multiple tests
	 * @param $payload
	 * @param $user
	 * @param $expected_status
	 * @return \Illuminate\Foundation\Testing\TestResponse
	 */
	private function testCreateSiteWithInvalidData($payload, $user, $expected_status)
	{
		echo "$user $expected_status\n";
		$response = $this->json(
			'POST',
			'/api/v1/sites',
			$payload,
			['Authorization' => 'Bearer ' . $this->$user->api_token]
		);
		$response->assertStatus($expected_status);
		$this->assertFalse($this->siteExistsWithHostAndPath($payload['host'], $payload['path']));
		return $response;
	}


}