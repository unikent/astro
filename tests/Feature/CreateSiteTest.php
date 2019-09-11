<?php

namespace Tests\Feature;

use App\Models\Definitions\SiteDefinition;

/**
 * Class CreateSiteTestV2
 * @package Tests\Feature
 */
class CreateSiteTest extends APICommandTestBase
{
    /**
     * Get the request method to use for this api command
     * @return string - The request method to use for this API request, e.g. GET, POST, DELETE.
     */
    public function requestMethod()
    {
        return 'POST';
    }

    /**
     * Get the api endpoint to test this command.
     * @return string - The API endpoint to test (usually starting with '/api/v1')
     */
    public function apiURL()
    {
        return '/api/v1/sites';
    }

    /**
     * Get the prefix used for filenames for fixture data for this command, e.g. "CreateSite",
     * @return string
     */
    public function fixtureDataPrefix()
    {
        return 'CreateSite';
    }

    /**
     * Utility method to confirm that the test has not modified the database. This is used as an additional
     * check when testing commands with invalid input or unauthorised users and should be implemented for each
     * api command test.
     * @param string $payload - The (json?) payload used to make the last request
     * @return bool
     */
    protected function fixturesAreUnchanged($payload)
    {
        return true;
    }

    /**
     * @test
     * @group api
     * @dataProvider validDataProvider
     */
    public function createSite_withSameHostAndPathAsExistingSite_failsWith422($payload)
    {
        // attempt to create the same site twice
        $this->makeRequestAndTestStatusCode($this->admin, $payload, 201);
        $response = $this->makeRequestAndTestStatusCode($this->admin, $payload, 422);
        $this->assertValidErrorResponseBody($response->getContent(), ['host']);
    }


    /**
     * @test
     * @group api
     * @dataProvider validDataProvider
     */
    public function createSite_withValidDataAndPermissions_createsASiteWithPagesBasedOnSiteDefinition($payload)
    {
        $response = $this->makeRequestAndTestStatusCode($this->admin, $payload, 201);
        $this->assertTrue($this->siteExistsWithHostAndPath($payload['host'], $payload['path']));
        $json = json_decode($response->getContent(), false);
        $this->assertValidJsonSchema($json, 'API/V1/CreateSite/201.json');
        $definition_id = $payload['site_definition']['name'] . '-v' . $payload['site_definition']['version'];
        $definition_filename = SiteDefinition::locateDefinition($definition_id);
        $definition = SiteDefinition::fromDefinitionFile($definition_filename);
        $this->assertSiteHasPageStructure($json->data->id, [$definition->defaultPages]);
    }

    /**
     * Tests that a site is created properly with only the homepage if create_default_pages option is set to false
     * @test
     * @group api
     * @dataProvider validDataProvider
     */
    public function createSite_withCreateDefaultPagesSetToFalse_onlyCreatesHomePage($payload)
    {
        // modify the payload
        $payload['create_default_pages'] = false;
        $response = $this->makeRequestAndTestStatusCode($this->admin, $payload, 201);
        $this->assertTrue($this->siteExistsWithHostAndPath($payload['host'], $payload['path']));
        $json = json_decode($response->getContent(), false);
        $this->assertValidJsonSchema($json, 'API/V1/CreateSite/201.json');
        $definition_id = $payload['site_definition']['name'] . '-v' . $payload['site_definition']['version'];
        $definition_filename = SiteDefinition::locateDefinition($definition_id);
        $definition = SiteDefinition::fromDefinitionFile($definition_filename);
        $expectedPages = $definition->defaultPages;
        $expectedPages['children'] = [];
        $this->assertSiteHasPageStructure($json->data->id, [$expectedPages]);
    }
}
