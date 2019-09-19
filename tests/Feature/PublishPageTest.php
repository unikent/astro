<?php

namespace Tests\Feature;

class PublishPageTest extends APICommandTestBase
{
	// the following user types / roles are able to publish
	public $authorizedUsers = [
		'admin',
		'owner',
		'editor',
	];

	// fixtures are currently just json payload
	// there isn't a payload for publishing
	// so we can't have an invalid payload!
	protected $skipInvalidFixtureTests = true;

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
		return '/api/v1/pages/' . $this->site->draftHomepage->id . '/publish';
	}

	/**
	 * Get the prefix used for filenames for fixture data for this command, e.g. "CreateSite",
	 * @return string
	 */
	public function fixtureDataPrefix()
	{
		return 'PublishPage';
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
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function publishPage_withValidUserAndPage_PublishesThePage($user)
	{
		$page = $this->site->draftHomepage;
		$current_revision = $page->revision;
		$response = $this->makeRequestAndTestStatusCode($this->$user, null, 200);
		$published_page = $page->publishedVersion();
		$published_revision = $published_page->revision;
		$this->assertNotNull($published_page);
		$this->assertEquals($current_revision->id, $published_page->revision_id);
		$this->assertNotEmpty($published_revision->published_at);
	}
}
