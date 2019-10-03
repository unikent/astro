<?php

namespace Tests\Feature;
use App\Models\Page;

class DeletePageTest extends APICommandTestBase
{
	// the following user types / roles are able to delete a page
	public $authorizedUsers = [
		'admin',
		'owner',
		'editor',
	];

	// fixtures are currently just json payload
	// there isn't a payload for publishing
	// so we can't have an invalid payload!
	protected $skipInvalidFixtureTests = true;

	// the page object we want to delete
	private $pageToDelete = null;

	/**
	 * Get the request method to use for this api command
	 * @return string - The request method to use for this API request, e.g. GET, POST, DELETE.
	 */
	public function requestMethod()
	{
		return 'DELETE';
	}

	/**
	 * Get the api endpoint to test this command.
	 * @return string - The API endpoint to test (usually starting with '/api/v1')
	 */
	public function apiURL()
	{
		$id = ($this->pageToDelete) ? $this->pageToDelete->id : $this->multiPageSite->draftHomepage->children[0]->id;
		return '/api/v1/pages/' . $id;
	}

	/**
	 * Get the prefix used for filenames for fixture data for this command, e.g. "CreateSite",
	 * @return string
	 */
	public function fixtureDataPrefix()
	{
		return 'DeletePage';
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
	 * @group wip
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function deletePage_withValidUserAndPage_DeletesThePage($user)
	{
		$this->pageToDelete = $this->multiPageSite->draftHomepage->children[0];
		$childrenBeforeDelete = count($this->multiPageSite->draftHomepage->children);
		$response = $this->makeRequestAndTestStatusCode($this->$user, null, 200);
		$this->multiPageSite->draftHomepage->refresh();
		$childrenAfterDelete = count($this->multiPageSite->draftHomepage->children);
		
		$this->assertNull(Page::find($this->pageToDelete->id));
		$this->assertNull($this->multiPageSite->draftHomepage->children->find($this->pageToDelete->id));
		$this->assertTrue($childrenBeforeDelete - $childrenAfterDelete === 1);
	}
}
