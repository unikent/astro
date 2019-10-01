<?php

namespace Tests\Feature;

/**
 * @package Tests\Feature
 */
class ValidateContentTest extends APICommandTestBase
{
	public $authorizedUsers = [
		'admin',
		'owner',
		'editor',
		'contributor',
	];

	/**
	 * Get the request method to use for this api command
	 * @return string - The request method to use for this API request, e.g. GET, POST, DELETE.
	 */
	public function requestMethod()
	{
		return 'PUT';
	}

	/**
	 * Get the api endpoint to test this command.
	 * @return string - The API endpoint to test (usually starting with '/api/v1')
	 */
	public function apiURL()
	{
		return '/api/v1/pages/' . $this->site->draftHomepage->id . '/content';
	}

	/**
	 * Get the prefix used for filenames for fixture data for this command, e.g. "CreateSite",
	 * @return string
	 */
	public function fixtureDataPrefix()
	{
		return 'ValidatePageContent';
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
	 * @dataProvider authorizedUsersProvider
	 * @param $user
	 */
	public function updateContent_withMissingOptionalField_Is_Valid($user)
	{
		/*
		$page = $this->site->draftHomepage;
		$revision = $page->revision;
		$old_revision_id = $page->revision_id;
		$payload = [
			'blocks' => $revision->blocks
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$page->refresh();
		$this->assertEquals($old_revision_id, $page->revision_id);
		*/
	}

}

/*
ideas...
if item has content then it should fit in min and max
if item has no content then it should be valid
if item has content and it is less than min then it should be invalid
if item has content and it is more than max then it should be invalid
*/
