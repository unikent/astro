<?php

namespace Tests\Feature;

/**
 * Feature tests for the add page api endpoint.
 * See the traits used for additional information about how these tests work.
 * @package Tests\Feature
 */
class UpdateContentTest extends APICommandTestBase
{
	public $authorizedUsers = [
		'admin',
		'owner',
		'editor',
		'contributor',
	];

	/**
	 * @test
	 * @group api
	 * @dataProvider authorizedUsersProvider
	 * @param $user
	 */
	public function updateContent_withUnchangedContent_doesNothing_butReturns200($user)
	{
		$page = $this->site->draftHomepage;
		$revision = $page->revision;
		$old_revision_id = $page->revision_id;
		$payload = [
			'blocks' => $revision->blocks
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$page->refresh();
		$this->assertEquals($old_revision_id, $page->revision_id);
	}

	/**
	 * @test
	 * @group api
	 * @dataProvider authorizedUsersProvider
	 * @param $user
	 */
	public function updateContent_withChangedContent_createsNewRevision_andUpdatesPageLastModified_andReturns200($user)
	{
		$page = $this->site->draftHomepage;
		$revision = $page->revision;
		$old_revision_id = $page->revision_id;
		$payload = [
			'blocks' => $revision->blocks
		];
		$payload['blocks']['test-region-v1'][0]['blocks'][0]['fields']['content'] = 'I am new content';
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$page->refresh();
		$page->load('revision');
		$this->assertNotEquals($old_revision_id, $page->revision_id);
		$new_revision = $page->revision;
		$updated_blocks = $new_revision->blocks;
		$this->assertEquals(
			'I am new content',
			$updated_blocks['test-region-v1'][0]['blocks'][0]['fields']['content']
		);
		$this->assertEquals($revision->revision_set_id, $new_revision->revision_set_id);
	}

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
		return 'UpdatePageContent';
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
}