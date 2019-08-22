<?php

namespace Tests\Feature;

use App\Models\Definitions\SiteDefinition;
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
		$this->assertValidErrorResponseBody($response->getContent(), ['host', 'path']);
	}

	/**
	 * @test
	 * @group api
	 * @dataProvider validSiteDataProvider
	 */
	public function createSite_withValidDataAndPermissions_createsASiteWithPagesBasedOnSiteDefinition($payload)
	{
		$response = $this->createSiteAndTestStatusCode($this->admin, $payload, 201);
		$this->assertTrue($this->siteExistsWithHostAndPath($payload['host'], $payload['path']));
		$json = json_decode($response->getContent(), false);
		$this->assertValidJsonSchema($json, 'API/V1/CreateSite/201.json');
		$definition_id = $payload['site_definition']['name'] . '-v' . $payload['site_definition']['version'];
		$definition_filename = SiteDefinition::locateDefinition($definition_id);
		$definition = SiteDefinition::fromDefinitionFile($definition_filename);
		$this->assertSiteHasPageStructure($json->data->id, [$definition->defaultPages]);
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
		$this->assertValidErrorResponseBody($response->getContent());
	}

	/**
	 * @group api
	 * @test
	 * @dataProvider unauthorizedUsersInvalidSiteDataProvider
	 */
	public function createSite_withInvalidData_failsWith403ForUnauthorizedUsers($payload, $user)
	{
		$response = $this->doTestCreateSiteWithInvalidData($payload, $user, 403);
		$this->assertValidErrorResponseBody($response->getContent());
	}

	/**
	 * @group api
	 * @test
	 * @dataProvider authorizedUsersInvalidSiteDataProvider
	 */
	public function createSite_withInvalidData_failsWith422ForAuthorizedUsers($payload, $user)
	{
		$response = $this->doTestCreateSiteWithInvalidData($payload, $user, 422);
		$this->assertValidErrorResponseBody($response->getContent(), true);
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
			['Authorization' => 'Bearer ' . $user->jwt]
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
	private function doTestCreateSiteWithInvalidData($payload, $user, $expected_status)
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
		return $this->combineForProvider($this->getValidFixtureData('CreateSite'), $this->unauthorizedUsers());
	}

	/**
	 * Data provider providing data that is invalid to create a site.
	 * @return array
	 */
	public function unauthorizedUsersInvalidSiteDataProvider()
	{
		return $this->combineForProvider($this->getInvalidFixtureData('CreateSite'), $this->unauthorizedUsers());
	}

	/**
	 * Data provider providing data that is invalid to create a site.
	 * @return array
	 */
	public function authorizedUsersInvalidSiteDataProvider()
	{
		return $this->combineForProvider($this->getInvalidFixtureData('CreateSite'), $this->authorizedUsers());
	}


}
