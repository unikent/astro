<?php
namespace Tests\Unit\Models\APICommands;

use Astro\API\Models\APICommands\PublishPage;

class PublishPageTest extends APICommandTestCase
{
    public function command()
    {
        return new PublishPage();
    }

    public function getValidData()
    {
        return [
            'name' => 'A Valid Name',
            'host' => 'example.com',
            'path' => '',
            'homepage_layout' => [
                'name' => 'test-layout',
                'version' => 1
            ]
        ];
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_returns_publishedVersionOfPage()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageIsNotDraft_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageDoesNotExist_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageIsMissingOrNull_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageParent_isUnpublished_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageIsDraftRoot_passes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageIsDraftWithPublishedParent_passes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_whenThisPageIsAlreadyPublishedHere_leavesPublishedDescendantsUnchanged()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_whenAnotherPageIsPublishedHere_replacesItAndItsDescendants()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_whenThisPageWasPreviouslyPublishedElsewhere_deletesOldLocationPageAndDescendants()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     * @todo break this up into multiple tests to handle different logic
     */
    public function execute_positionsPublishedPageCorrectly_withRegardToItsSiblings()
    {
        $this->markTestIncomplete();
    }


    /**
	 * @test
     * @group APICommands
	 */
	public function publish_WhenPageHasUnsavedChanges_ThrowsException()
	{
        $this->markTestIncomplete(
            'Currently publish only works on a saved version of the page. This test may not be needed.'
        );
	}

	/**
	 * @test
	 * @group APICommands
	 */
	public function publish_WhenPageHasUnpublishedParents_ThrowsUnpublishedParentException()
	{
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function execute_CreatesPageWithVersionPublished_ifNotPreviouslyPublished()
	{
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function publish_AssociatesCurrentRevision_withPublishedPage_andUpdatesRevisionsPublishedDate()
	{
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 * @group ignore
	 * @group integration
	 *
	 * This tests integration between $page->publish(), $route->makeActive() and $route->delete().
	 */
	public function publish_WhenThereIsAnActiveRoute_ActiveRouteGetsRedirected()
	{
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}


}
