<?php

namespace Tests\Feature;
use App\Models\Page;
use App\Models\DeletedPage;
use App\Models\LocalAPIClient;

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
		$this->assertTrue($childrenBeforeDelete - 1 === $childrenAfterDelete);
		$this->assertNotNull(DeletedPage::where('path','=',$this->pageToDelete->path)->where('revision_id','=',$this->pageToDelete->revision_id)->first());	
	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function deletePage_withValidUserAndPublishedPage_DeletesThePage($user)
	{
		$this->pageToDelete = $this->publishableMultiPageSite->draftHomepage->children[1];
		$api = new LocalAPIClient($this->admin);
		$api->publishPage($this->publishableMultiPageSite->draftHomepage->id);
		$api->publishPage($this->pageToDelete->id);

		$childrenBeforeDelete = count($this->publishableMultiPageSite->draftHomepage->children);
		$response = $this->makeRequestAndTestStatusCode($this->$user, null, 200);
		$this->publishableMultiPageSite->draftHomepage->refresh();
		$childrenAfterDelete = count($this->publishableMultiPageSite->draftHomepage->children);

		$this->assertNull(Page::find($this->pageToDelete->id));
		$this->assertNull($this->publishableMultiPageSite->draftHomepage->children->find($this->pageToDelete->id));
		$this->assertTrue($childrenBeforeDelete - 1 === $childrenAfterDelete);
		$this->assertNotNull(DeletedPage::where('path','=',$this->pageToDelete->path)->where('revision_id','=',$this->pageToDelete->revision_id)->first());	
	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function deletePage_withValidUserAndPublishedPageWithChildren_DeletesThePage($user)
	{
		$this->pageToDelete = $this->publishableMultiPageSite->draftHomepage->children[0];

		foreach ($this->publishableMultiPageSite->draftHomepage->descendantsAndSelf()->get() as $descendant) {
			$api = new LocalAPIClient($this->admin);
			$api->publishPage($descendant->id);
			// check that they have been published
			$this->assertNotNull(
				Page::findBySiteAndPath($this->publishableMultiPageSite->id, $descendant->path, Page::STATE_PUBLISHED)
			);
		}

		$deletedPages = [];
		foreach ($this->pageToDelete->descendantsAndSelf()->get() as $descendantToDelete) {
			$deletedPages[] = [
				'id' => $descendantToDelete->id,
				'path' => $descendantToDelete->path,
				'draft_revision_id' => $descendantToDelete->revision_id,
				'published_revision_id' => $descendantToDelete->publishedVersion()->revision_id
			];
		}

		$descendantsBeforeDelete = count($this->publishableMultiPageSite->draftHomepage->descendants()->get());
		$response = $this->makeRequestAndTestStatusCode($this->$user, null, 200);
		$this->publishableMultiPageSite->draftHomepage->refresh();
		$descendantsAfterDelete = count($this->publishableMultiPageSite->draftHomepage->descendants()->get());

		$this->assertNull(Page::find($this->pageToDelete->id));
		$this->assertNull($this->publishableMultiPageSite->draftHomepage->children->find($this->pageToDelete->id));
		$this->assertTrue($descendantsBeforeDelete - $descendantsAfterDelete === 4);
		foreach ($deletedPages as $deletedPage) {
			$this->assertNotNull(
				DeletedPage::where('path','=',$deletedPage['path'])
					->where('revision_id','=',$deletedPage['draft_revision_id'])
					->first()
			);
			//check that published pages have been unpublished
			$this->assertNull(
				Page::findBySiteAndPath($this->publishableMultiPageSite->id, $deletedPage['path'], Page::STATE_PUBLISHED)
			);
		}

	}

	/**
	 * @test
	 * @group api
	 * @param $user
	 * @dataProvider authorizedUsersProvider
	 */
	public function deletePage_withValidUserAndUnPublishedPageWithChildren_DeletesThePage($user)
	{
		$this->pageToDelete = $this->publishableMultiPageSite->draftHomepage->children[0];

		$deletedPages = [];
		foreach ($this->pageToDelete->descendantsAndSelf()->get() as $descendantToDelete) {
			$deletedPages[] = [
				'id' => $descendantToDelete->id,
				'path' => $descendantToDelete->path,
				'revision_id' => $descendantToDelete->revision_id,
			];
		}

		$descendantsBeforeDelete = count($this->publishableMultiPageSite->draftHomepage->descendants()->get());
		$response = $this->makeRequestAndTestStatusCode($this->$user, null, 200);
		$this->publishableMultiPageSite->draftHomepage->refresh();
		$descendantsAfterDelete = count($this->publishableMultiPageSite->draftHomepage->descendants()->get());

		$this->assertNull(Page::find($this->pageToDelete->id));
		$this->assertNull($this->publishableMultiPageSite->draftHomepage->children->find($this->pageToDelete->id));
		$this->assertTrue($descendantsBeforeDelete - $descendantsAfterDelete === 4);
		foreach ($deletedPages as $deletedPage) {
			$this->assertNotNull(
				DeletedPage::where('path','=',$deletedPage['path'])
					->where('revision_id','=',$deletedPage['revision_id'])
					->first()
			);
		}

	}



}
