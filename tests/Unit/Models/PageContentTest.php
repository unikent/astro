<?php
namespace Tests\Unit\Models;

use Mockery;
use Exception;
use Tests\TestCase;
use App\Models\Block;
use App\Models\Page;
use App\Http\Transformers\Api\v1\PageTransformer;
use App\Models\Redirect;
use App\Models\Revision;
use App\Models\Definitions\Layout as LayoutDefinition;

class PageContentTest extends TestCase
{

	/**
	 * @test
	 * @group ignore
	 */
	public function publish_WhenPageHasUnsavedChanges_ThrowsException()
	{
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function publish_WhenPageHasUnpublishedParents_ThrowsException()
	{
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function publish_CreatesPublishedPageInstance()
	{
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function publish_AssociatesPublishedPageWithPageInstance()
	{
        return $this->markTestIncomplete(
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
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function publish_DraftRouteBecomesActiveRoute()
	{
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function publish_PageOnlyHasOneRoute()
	{
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function publish_PublishedPageBakeContainsSerializedPageInstance()
	{
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}



	/**
	 * @test
	 * @group ignore
	 */
	public function revert_WhenPublishedPageIsNotAssociatedWithPage_ThrowsException()
	{
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $r1 = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
		$r1->page->publish(new PageTransformer);

		$r2 = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
		$r2->page->publish(new PageTransformer);

		$this->expectException(Exception::class);
		$r1->page->revert($r2->page->published);
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function revert_RevertsPageToMatchPublishedPage()
	{
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
		$route = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
		$page = $route->page;

		$page->publish(new PageTransformer);

		$title = $page->title;
		$page->title = 'Foobar';

		$layout = $page->layout_name;
		$page->layout_name = 'fizzbuzz17';

		$page->save();
		$this->assertEquals('Foobar', $page->title);
		$this->assertEquals('fizzbuzz17', $page->layout_name);

		$page->revert($page->published);
		$this->assertEquals($title, $page->title);
		$this->assertEquals($layout, $page->layout_name);
	}

	/**
	 * @test
	 * @group ignore
	 */
	public function revert_RevertsBlocksToMatchPublishedPage()
	{
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
		$route = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
		$page = $route->page;

		$blocks = factory(Block::class, 2)->create([ 'page_id' => $page->getKey(), 'region_name' => 'test-region' ]);
		$page->publish(new PageTransformer);

		$moreBlocks = factory(Block::class, 3)->create([ 'page_id' => $page->getKey(), 'region_name' => 'test-region' ]);

		$page = $page->fresh();
		$this->assertCount(5, $page->blocks);

		$page->revert($page->published);
		$this->assertCount(2, $page->blocks);
	}




}
