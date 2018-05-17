<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Feature\Traits\MakesAssertionsAboutErrors;
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
		MakesAssertionsAboutErrors,
		LoadsFixtureData;

	/**
	 * @test
	 * @group api
	 * @dataProvider validSiteDataProvider
	 */
	public function createSite_withSameHostAndPathAsExistingSite_failsWith422($payload)
	{
		// attempt to create the same site twice
		$this->createSiteAndTestStatusCode($this->admin, $payload, 201);
		$response = $this->createSiteAndTestStatusCode($this->admin, $payload, 422);
		$json = json_decode($response->getContent(), true);
		$this->assertValidErrorResponseBody($json, ['host', 'path']);
	}

	/**
	 * @test
	 * @group api
	 * @dataProvider validSiteDataProvider
	 */
	public function createSite_withValidDataAndPermissions_createsASiteWithPagesBasedOnSiteDefinition($data)
	{
		$response = $this->createSiteAndTestStatusCode($this->admin, $data, 201);
		$this->assertTrue($this->siteExistsWithHostAndPath($data['host'], $data['path']));
		// @todo - test that response json is valid
		$json = json_decode($response->getContent(), true);
	}

	/**
	 * @group api
	 * @test
	 * @dataProvider validDataUnauthorizedUsersProvider
	 */
	public function createSite_withValidDataButInvalidPrivileges_failsWith403($payload, $user)
	{
		$response = $this->createSiteAndTestStatusCode($this->$user, $payload, 403);
		$this->assertFalse($this->siteExistsWithHostAndPath($payload['host'], $payload['path']));
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
	 * @group api
	 * @test
	 * @dataProvider authorizedUsersInvalidSiteDataProvider
	 */
	public function createSite_withInvalidData_failsWith422ForAuthorizedUsers($payload, $user)
	{
		$response = $this->testCreateSiteWithInvalidData($payload, $user, 422);
		// @todo - test that response json is valid
		$json = json_decode($response->getContent(), true);
		$this->assertValidErrorResponseBody($json, true);
	}

	/*****************************
	 *
	 * Utility Methods
	 *
	 *****************************/

	/**
	 * Utility method to send create site request and test response code
	 * @param User $user - The user account whose api token to use.
	 * @param array $payload - Array representation of the json data to use as the request payload.
	 * @param int $expected_status - The expected http status code for the response.
	 * @return \Illuminate\Foundation\Testing\TestResponse
	 */
	private function createSiteAndTestStatusCode($user, $payload, $expected_status)
	{
		$response = $this->json(
			'POST',
			'/api/v1/sites',
			$payload,
			['Authorization' => 'Bearer ' . $user->api_token]
		);
		$response->assertStatus($expected_status);
		return $response;
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
		$response = $this->createSiteAndTestStatusCode($this->$user, $payload, $expected_status);
		$this->assertFalse($this->siteExistsWithHostAndPath($payload['host'], $payload['path']));
		return $response;
	}

	/*******************
	 *
	 * Data Providers
	 *
	 *******************/

	/**
	 * Provides valid data for creating a site with.
	 * @return array
	 */
	public function validSiteDataProvider()
	{
		return $this->combineForProvider($this->getValidFixtureData('CreateSite'));
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
	 * Data provider providing data that is invalid to create a site.
	 * @return array
	 */
	public function unauthorizedUsersInvalidSiteDataProvider()
	{
		$users = ['randomer', 'contributor', 'editor', 'owner'];
		return $this->combineForProvider($this->getInvalidFixtureData('CreateSite'), array_combine($users, $users));
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


}