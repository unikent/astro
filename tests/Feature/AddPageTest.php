<?php

namespace Tests\Feature;

use Tests\Feature\Traits\MakesAssertionsAboutPages;
use Tests\Feature\Traits\CreatesFeatureFixtures;
use Tests\Feature\Traits\ExtractsPageAttributesFromPageJson;
use Tests\TestCase;

/**
 * Feature tests for the add page api endpoint.
 * See the traits used for additional information about how these tests work.
 * @package Tests\Feature
 */
class AddPageTest extends APICommandTestBase
{
	/**
	 * @test
	 * @group api
	 * @dataProvider authorizedUsersProvider
	 */
    public function addPageToParentAddsAtEndOfSiblings($user)
	{
		$response = $this->json('POST', '/api/v1/pages', [
				'title' => 'Add At End',
				'parent_id' => $this->site->draftHomepage->id,
				'slug' => 'add-at-end',
				'layout' => [
					'name' => 'test-layout',
					'version' => 1
				]
			],
			[
				'Authorization' => 'Bearer ' . $this->$user->api_token
			]
		);
		// Page has been created
		$response->assertStatus(201);
		$response->assertJson([
				'data' => [
				'title' => 'Add At End',
				'parent_id' => $this->site->draftHomepage->id,
				'slug' => 'add-at-end'
			]
		]);
		$this->assertTrue($this->pageIsLastChildOf($this->getPageId($response->json()['data']), $this->site->draftHomepage->id));
	}

	public function addPageBeforePageAddsPageBeforeThatPage()
	{
		$this->markTestIncomplete();
	}

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
        return '/api/v1/pages';
    }

    /**
     * Get the prefix used for filenames for fixture data for this command, e.g. "CreateSite",
     * @return string
     */
    public function fixtureDataPrefix()
    {
        return 'AddPage';
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

    public function fixtureDataSearchReplace()
    {
        return [
            '%homepage_id%' => $this->site->draftHomepage->id
        ];
    }
}
