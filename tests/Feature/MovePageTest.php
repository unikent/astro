<?php

namespace Tests\Feature;
use App\Models\Page;
use App\Models\LocalAPIClient;

class MovePageTest extends APICommandTestBase
{
	// the following user types / roles are able to move a page
	public $authorizedUsers = [
		'admin',
		'owner',
		'editor',
	];

	// the page object we want to delete
	private $pageToMove = null;

	// the site we are currently working with
	private $activeSite = null;

	// request method
	private $requestMethod = 'PATCH';

	/**
	 * Get the request method to use for this api command
	 * @return string - The request method to use for this API request, e.g. GET, POST, DELETE.
	 */
	public function requestMethod()
	{
		return $this->requestMethod;
	}

	/**
	 * Get the api endpoint to test this command.
	 * @return string - The API endpoint to test (usually starting with '/api/v1')
	 */
	public function apiURL()
	{
		$site_id = ($this->activeSite) ? $this->activeSite->id : $this->multiPageSite->id;
		return '/api/v1/sites/'.$site_id.'/tree';
	}

	/**
	 * Get the prefix used for filenames for fixture data for this command, e.g. "CreateSite",
	 * @return string
	 */
	public function fixtureDataPrefix()
	{
		return 'MovePage';
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
	public function movePage_whenAttemptingToMoveAHomepage_Fails($user)
	{
		$this->pageToMove = $this->multiPageSite->draftHomepage;
		$payload = [
			'page_id' => $this->pageToMove->id,
			'parent_id' => $this->multiPageSite->draftHomepage->children[0]->id,
			'next_id' => null
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 422);
		$this->multiPageSite->draftHomepage->refresh();

		$this->assertEquals($this->multiPageSite->draftHomepage->id, $this->pageToMove->id);
	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function movePage_toLastPositionWithValidUserAndPage_MovesThePageToLastPosition($user)
	{
		$num_of_children = count($this->multiPageSite->draftHomepage->children);
		$this->pageToMove = $this->multiPageSite->draftHomepage->children[0];
		$payload = [
			'page_id' => $this->pageToMove->id,
			'parent_id' => $this->multiPageSite->draftHomepage->id,
			'next_id' => null
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$this->multiPageSite->draftHomepage->refresh();

		$lastChild = $this->multiPageSite->draftHomepage->children[$num_of_children - 1];

		$this->assertEquals($lastChild->id, $this->pageToMove->id);
	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function movePage_toFirstPositionWithValidUserAndPage_MovesThePageToFirstPosition($user)
	{
		$num_of_children = count($this->multiPageSite->draftHomepage->children);
		$this->pageToMove = $this->multiPageSite->draftHomepage->children[$num_of_children -1 ];
		$payload = [
			'page_id' => $this->pageToMove->id,
			'parent_id' => $this->multiPageSite->draftHomepage->id,
			'next_id' => $this->multiPageSite->draftHomepage->children[0]->id
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$this->multiPageSite->draftHomepage->refresh();

		$firstChild = $this->multiPageSite->draftHomepage->children[0];

		$this->assertEquals($firstChild->id, $this->pageToMove->id);
	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function movePage_toMiddlePositionWithValidUserAndPage_MovesThePageToMiddlePosition($user)
	{
		$num_of_children = count($this->multiPageSite->draftHomepage->children);
		$this->pageToMove = $this->multiPageSite->draftHomepage->children[0];
		$payload = [
			'page_id' => $this->pageToMove->id,
			'parent_id' => $this->multiPageSite->draftHomepage->id,
			'next_id' => $this->multiPageSite->draftHomepage->children[$num_of_children - 1]->id
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$this->multiPageSite->draftHomepage->refresh();

		$middleChild = $this->multiPageSite->draftHomepage->children[$num_of_children - 2];

		$this->assertEquals($middleChild->id, $this->pageToMove->id);
	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function movePage_toChildOfSiblingWithValidUserAndPage_MovesThePageToChildOfSibling($user)
	{
		$num_of_children = count($this->multiPageSite->draftHomepage->children);
		$this->pageToMove = $this->multiPageSite->draftHomepage->children[$num_of_children - 2];
		$payload = [
			'page_id' => $this->pageToMove->id,
			'parent_id' => $this->multiPageSite->draftHomepage->children[$num_of_children - 1]->id,
			'next_id' => null
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$this->multiPageSite->draftHomepage->refresh();

		$new_num_of_children = count($this->multiPageSite->draftHomepage->children);
		$movedPage = $this->multiPageSite->draftHomepage->children[$new_num_of_children - 1]->children[0];

		$this->assertEquals($new_num_of_children, $num_of_children - 1);
		$this->assertEquals($movedPage->id, $this->pageToMove->id);
	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function movePage_toSiblingOfParentWithValidUserAndPage_MovesThePageToSiblingOfParent($user)
	{
		$num_of_children = count($this->publishableMultiPageSite->draftHomepage->children);

		$this->pageToMove = $this->publishableMultiPageSite->draftHomepage->children[0]->children[0];
		$payload = [
			'page_id' => $this->pageToMove->id,
			'parent_id' => $this->publishableMultiPageSite->draftHomepage->id,
			'next_id' => $this->publishableMultiPageSite->draftHomepage->children[0]->id
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$this->publishableMultiPageSite->draftHomepage->refresh();

		$new_num_of_children = count($this->publishableMultiPageSite->draftHomepage->children);
		$movedPage = $this->publishableMultiPageSite->draftHomepage->children[0];

		$this->assertEquals($new_num_of_children, $num_of_children + 1);
		$this->assertEquals($movedPage->id, $this->pageToMove->id);
	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function movePage_withDecendantsAndWithValidUserAndPage_MovesPageAndKeepsDescendants($user)
	{
		$num_of_children = count($this->publishableMultiPageSite->draftHomepage->children[0]->children);

		$this->pageToMove = $this->publishableMultiPageSite->draftHomepage->children[0];
		$payload = [
			'page_id' => $this->pageToMove->id,
			'parent_id' => $this->publishableMultiPageSite->draftHomepage->id,
			'next_id' => null
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$this->publishableMultiPageSite->draftHomepage->refresh();

		$movedPage = $this->publishableMultiPageSite->draftHomepage->children[count($this->publishableMultiPageSite->draftHomepage->children) - 1];
		$new_num_of_children = count($movedPage->children);

		$this->assertEquals($new_num_of_children, $num_of_children);
		$this->assertEquals($movedPage->id, $this->pageToMove->id);
		$this->assertEquals($movedPage->children[0]->id, $this->pageToMove->children[0]->id);
	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function movePage_withValidPublishedRevisionAndWithValidUser_MovesPageAndKeepsPublishedRevision($user)
	{
		/*
		Given
			a published home page
			a first child which is published
		When
			move the published child to last position
		Then
			the published child is in the final position
			the published child is still published
			the published revision remains the same

		*/
		$this->pageToMove = $this->publishableMultiPageSite->draftHomepage->children[0];
		$api = new LocalAPIClient($this->admin);
		$api->publishPage($this->publishableMultiPageSite->draftHomepage->id);
		$api->publishPage($this->pageToMove->id);
		$originalPublishedRevisionID = $this->pageToMove->publishedVersion()->revision_id;
		$payload = [
			'page_id' => $this->pageToMove->id,
			'parent_id' => $this->publishableMultiPageSite->draftHomepage->id,
			'next_id' => null
		];
		$response = $this->makeRequestAndTestStatusCode($this->$user, $payload, 200);
		$this->publishableMultiPageSite->draftHomepage->refresh();

		$movedPage = $this->publishableMultiPageSite->draftHomepage->children[count($this->publishableMultiPageSite->draftHomepage->children) - 1];

		$this->assertEquals($movedPage->id, $this->pageToMove->id);
		$this->assertEquals($movedPage->publishedVersion()->revision_id, $movedPage->draftVersion()->revision_id);
		$this->assertEquals($movedPage->publishedVersion()->revision_id, $originalPublishedRevisionID);
	}

}
