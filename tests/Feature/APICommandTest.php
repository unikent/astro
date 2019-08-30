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
 * Generic Feature tests for api endpoints.
 * See the traits used for additional information about how these tests work.
 * Tests the following generic conditions for API calls:
 * - Request with an invalid jwt OR without bearer token fails with 401 response code regardless of whether request data is valid or not.
 *   - Expected 401 response code
 *   - Optionally test that database structure hasn't been modified based on the request
 * - Invalid request payload with authorized user
 *   - Expected 422 response code
 *   - Response has standard error format
 *   - Optionally test if error has correct error message fields for that payload
 *   - Optionally test that database structure hasn't been modified based on the request
 * - Valid or invalid request payload with unauthorized user
 *   - Expected 403 response code
 *   - Response has expected error format for 403
 *   - Optionally test that database structure hasn't been modified based on the request
 * - Valid request payload with authorized users
 *   - Expected response code
 *   - Expected response data format / json schema
 *   - Optionally confirm that the underlying database structure has changed as expected
 * @package Tests\Feature
 */
abstract class APICommandTest extends TestCase
{
    use CreatesFeatureFixtures,
        ExtractsPageAttributesFromPageJson,
        MakesAssertionsAboutSites,
        MakesAssertionsAboutPages,
        ValidatesJsonSchema,
        MakesAssertionsAboutErrors,
        LoadsFixtureData;

    /******************************************************************************************************
     * Abstract Methods
     ******************************************************************************************************/

    /**
     * Get the request method to use for this api command
     * @return string - The request method to use for this API request, e.g. GET, POST, DELETE.
     */
    abstract public function requestMethod();

    /**
     * Get the api endpoint to test this command.
     * @return string - The API endpoint to test (usually starting with '/api/v1')
     */
    abstract public function apiURL();

    /**
     * Get the prefix used for filenames for fixture data for this command, e.g. "CreateSite",
     * @return string
     */
    abstract public function fixtureDataPrefix();

    /**
     * Utility method to confirm that the test has not modified the database. This is used as an additional
     * check when testing commands with invalid input or unauthorised users and should be implemented for each
     * api command test.
     * @param string $payload - The (json?) payload used to make the last request
     * @return bool
     */
    abstract protected function fixturesAreUnchanged($payload);


    /*******************************************************************************************************
     * Tests
     *******************************************************************************************************/

    /**
     * Tests that this command fails with a 401 for an invalid JWT with valid request format
     * @param string $payload Valid payload for this command
     * @dataProvider validDataProvider
     */
    public function request_withValidData_andInvalidJWT_failsWith401($payload)
    {
        // makerequestandteststatuscode just uses the jwt property of the given user
        $user = new User();
        $user->jwt = 'kjadfkjdsf;kja398098ae9083odsaukdsf;j;lsfkjakjdsf83a048afd;a;fdja;ljfd;ljakdfja;jdkfau903uraidjflajfd;a;dfj';
        $response = $this->makeRequestAndTestStatusCode($user, $payload, 401);
        $this->assertTrue($this->fixturesAreUnchanged($payload));
        $this->assertValidErrorResponseBody($response->getContent());
    }

    /**
     * Tests that this command fails with a 401 for an invalid JWT with invalid request data
     * @param string $payload Invalid payload for this command
     * @dataProvider invalidDataProvider
     */
    public function request_withInvalidData_andInvalidJWT_failsWith401($payload)
    {
        // makerequestandteststatuscode just uses the jwt property of the given user
        $user = new User();
        $user->jwt = 'kjadfkjdsf;kja398098ae9083odsaukdsf;j;lsfkjakjdsf83a048afd;a;fdja;ljfd;ljakdfja;jdkfau903uraidjflajfd;a;dfj';
        $response = $this->makeRequestAndTestStatusCode($user, $payload, 401);
        $this->assertTrue($this->fixturesAreUnchanged($payload));
        $this->assertValidErrorResponseBody($response->getContent());
    }

    /**
     * Tests that this command fails with a 401 for no bearer token with valid request format
     * @param string $payload Valid payload for this command
     * @dataProvider validDataProvider
     */
    public function request_withValidData_andNoBearerToken_failsWith401($payload)
    {
        $response = $this->makeRequestAndTestStatusCode(null, $payload, 401, false);
        $this->assertTrue($this->fixturesAreUnchanged($payload));
        $this->assertValidErrorResponseBody($response->getContent());
    }

    /**
     * Tests that this command fails with a 401 for no bearer token with invalid request data
     * @param string $payload Invalid payload for this command
     * @dataProvider invalidDataProvider
     */
    public function request_withInvalidData_andNoBearerToken_failsWith401($payload)
    {
        $response = $this->makeRequestAndTestStatusCode(null, $payload, 401, false);
        $this->assertTrue($this->fixturesAreUnchanged($payload));
        $this->assertValidErrorResponseBody($response->getContent());
    }

    /**
     * Tests the response to running this command for each user type that shouldn't be allowed
     * @group api
     * @test
     * @dataProvider validDataUnauthorizedUsersProvider - Provides a json payload to send for each request for each user type
     */
    public function request_withValidData_andUnauthorizedUsers_failsWith403($payload, $user)
    {
        $response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 403);
        $this->assertTrue($this->fixturesAreUnchanged($payload));
        $this->assertValidErrorResponseBody($response->getContent());
    }

    /**
     * @group api
     * @test
     * @dataProvider invalidDataUnauthorizedUsersProvider
     */
    public function request_withInvalidData_andUnauthorizedUsers_failsWith403($payload, $user)
    {
        $response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 403);
        $this->assertTrue($this->fixturesAreUnchanged($payload));
        $this->assertValidErrorResponseBody($response->getContent());
    }

    /**
     * @group api
     * @test
     * @dataProvider invalidDataAuthorizedUsersProvider
     */
    public function request_withInvalidData_andAuthorizedUsers_failsWith422($payload, $user)
    {
        $response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 422);
        $this->assertTrue($this->fixturesAreUnchanged($payload));
        $this->assertValidErrorResponseBody($response->getContent(), true);
    }

    /***********************************************************************************************
     * Utility Methods
     **********************************************************************************************/

    /**
     * Utility method to send an api request and test response code
     * @param User $user - The user account whose api token to use.
     * @param array $payload - Array representation of the json data to use as the request payload.
     * @param int $expected_status - The expected http status code for the response.
     * @param bool $without_token - Whether or not to send bearer token with this request. Defaults to true.
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function makeRequestAndTestStatusCode($user, $payload, $expected_status, $with_token = true)
    {
        $headers = [];
        if($with_token) {
            $headers['Authorization'] = 'Bearer ' . $user->jwt;
        }

        $response = $this->json(
            $this->requestMethod(),
            $this->apiURL(),
            $payload,
            $headers
        );
        $response->assertStatus($expected_status);
        return $response;
    }

    /*****************************************************************************************************
     * Data Providers
     *****************************************************************************************************/

    /**
     * Data provider with valid data but users who should not be able to run this command
     * @return array [ [ $json_data, $username ], ... ]
     */
    public function validDataUnauthorizedUsersProvider()
    {
        return $this->combineForProvider($this->getValidFixtureData($this->fixtureDataPrefix()), $this->unauthorizedUsers());
    }

    /**
     * Data provider providing data that is invalid for this command together with users who are unauthorized to run the command
     * @return array
     */
    public function invalidDataUnauthorizedUsersProvider()
    {
        return $this->combineForProvider($this->getInvalidFixtureData($this->fixtureDataPrefix()), $this->unauthorizedUsers());
    }

    /**
     * Data provider providing data that is invalid for this command and authorized users.
     * @return array
     */
    public function invalidDataAuthorizedUsersProvider()
    {
        return $this->combineForProvider($this->getInvalidFixtureData($this->fixtureDataPrefix()), $this->authorizedUsers());
    }

    /**
     * Provides valid data for this command.
     * @return array
     */
    public function validDataProvider()
    {
        return $this->combineForProvider($this->getValidFixtureData($this->fixtureDataPrefix()));
    }

    /**
     * Provides invalid data for this command
     * @return array
     */
    public function invalidDataProvider()
    {
        return $this->combineForProvider($this->getInvalidFixtureData($this->fixtureDataPrefix()));
    }
}
