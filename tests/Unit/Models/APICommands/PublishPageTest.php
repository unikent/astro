<?php
namespace Tests\Unit\Models\APICommands;

use App\Models\APICommands\PublishPage;

class PublishPageTest extends APICommandTestCase
{
    public function fixture()
    {
        return new PublishPage();
    }

    public function getValidData()
    {
        return [
            'name' => 'A Valid Name',
            'publishing_group_id' => factory(\App\Models\PublishingGroup::class)->create()->getKey(),
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
