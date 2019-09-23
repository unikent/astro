<?php

namespace Tests\Feature;

/**
 * Feature tests for the add page api endpoint.
 * See the traits used for additional information about how these tests work.
 * @package Tests\Feature
 */
class AddPageTest extends APICommandTestBase
{
	public $authorizedUsers = [
		'admin',
		'owner',
	];

	/**
	 * @test
	 * @group api
	 * @dataProvider authorizedUsersProvider
	 */
    public function addPageToParent_AddsAtEndOfSiblings($user)
	{
		$parent_id = $this->site->draftHomepage->id;
		$response = $this->json('POST', '/api/v1/pages', [
				'title' => 'Add At End',
				'parent_id' => $parent_id,
				'slug' => 'add-at-end',
				'layout' => [
					'name' => 'test-layout',
					'version' => 1
				]
			],
			[
				'Authorization' => 'Bearer ' . $this->$user->jwt
			]
		);
		// Page has been created
		$response->assertStatus(201);
		$response->assertJson([
				'data' => [
				'title' => 'Add At End',
				'parent_id' => $parent_id,
				'slug' => 'add-at-end'
			]
		]);
		$this->assertTrue($this->pageIsLastChildOf($this->getPageId($response->json()['data']), $parent_id));
	}

	/**
	 * @test
	 * @group api
	 * @dataProvider authorizedUsersProvider
	 */
	public function addPageBeforePage_AddsPageBeforeThatPage($user)
	{
		$parent_id = $this->multiPageSite->draftHomepage->id;
		$sibling_id = $this->multiPageSite->draftHomepage->children[0]->id;
		$response = $this->json('POST', '/api/v1/pages', [
			'title' => 'Add Before',
			'parent_id' => $parent_id,
			'slug' => 'add-at-beginning',
			'next_id' => $sibling_id,
			'layout' => [
				'name' => 'test-layout',
				'version' => 1
			]
		],
			[
				'Authorization' => 'Bearer ' . $this->$user->jwt
			]
		);
		// Page has been created
		$response->assertStatus(201);
		$response->assertJson([
			'data' => [
				'title' => 'Add Before',
				'parent_id' => $parent_id,
				'slug' => 'add-at-beginning'
			]
		]);
		$page_id = $this->getPageId($response->json()['data']);
		$this->assertTrue($this->pageIsFirstChildOf($page_id, $parent_id));
		$this->assertTrue($this->pageIsPreviousSiblingOf($page_id, $sibling_id));
	}

	/**
	 * @test
	 * @group api
	 * @dataProvider authorizedUsersProvider
	 */
	public function addPageBeforePage_withHomepageAsNextPage_FailsWith422($user)
	{
		$parent_id = $this->multiPageSite->draftHomepage->id;
		$sibling_id = $parent_id;
		$response = $this->json('POST', '/api/v1/pages', [
			'title' => 'Add Before',
			'parent_id' => $parent_id,
			'slug' => 'add-at-beginning',
			'next_id' => $sibling_id,
			'layout' => [
				'name' => 'test-layout',
				'version' => 1
			]
		],
			[
				'Authorization' => 'Bearer ' . $this->$user->jwt
			]
		);
		// Page has been created
		$response->assertStatus(422);
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
	 * @test
	 * @group api
	 * @dataProvider authorizedUsersProvider
	 */
	public function addPage_withValidDefaultData_createsValidPage($user)
	{
		// given we have a site with a valid homepage
		$site = $this->publishableSite;
		$parent_id = $site->draftHomepage->id;


		// when we add a new page with valid defaults
		$payload = [
			'title' => 'Valid by default',
			'parent_id' => $parent_id,
			'slug' => 'valid-page',
			'layout' => [
				'name' => 'layout-with-valid-region-with-valid-block',
				'version' => 1
			]
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 201);
		$new_page_info = json_decode($response->getContent(), true);
		$new_page_id = $new_page_info['data']['id'];

		// then that page should be valid
		$this->pageIsValid($new_page_id);
	}

	/**
	 * @test
	 * @group api
	 * @dataProvider authorizedUsersProvider
	 */
	public function addPage_withInvalidDefaultData_createsInvalidPage($user)
	{
		// given we have a site with a valid homepage
		$site = $this->publishableSite;
		$parent_id = $site->draftHomepage->id;

		// when we add a new page with invalid defaults
		$payload = [
			'title' => 'Invalid by default',
			'parent_id' => $parent_id,
			'slug' => 'valid-page',
			'layout' => [
				'name' => 'layout-with-invalid-region-with-valid-block',
				'version' => 1
			]
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 201);
		$new_page_info = json_decode($response->getContent(), true);
		$new_page_id = $new_page_info['data']['id'];

		// then that page should be invalid
		$this->pageIsInvalid($new_page_id);
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
	 * Use correct parent_ids in fixture data
	 * @param array $input
	 * @param string $test
	 * @return array
	 */
    protected function modifyFixtureData($input, $test)
	{
		if($input['parent_id']) {
			$input['parent_id'] = $this->site->draftHomepage->id;
		}
		return $input;
	}
}
