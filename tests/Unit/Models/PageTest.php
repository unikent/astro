<?php
namespace Tests\Unit\Models;

use Mockery;
use Exception;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Block;
use App\Models\Route;
use App\Models\Redirect;
use App\Models\PublishedPage;
use App\Http\Transformers\Api\v1\PageTransformer;
use App\Models\Definitions\Layout as LayoutDefinition;

class PageTest extends TestCase
{

	/**
	 * @test
	 * @group wip
	 */
	public function publish_WhenPageHasUnsavedChanges_ThrowsException()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$route->page->title = 'Foobar!';

		$this->expectException(Exception::class);
		$route->page->publish(new PageTransformer);
	}

	/**
	 * @test
	 * @group wip
	 */
	public function publish_WhenPageHasUnpublishedParents_ThrowsException()
	{
		$parent = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$child = factory(Route::class)->states([ 'withPage' ])->create([ 'parent_id' => $parent->getKey() ]);

		$this->expectException(Exception::class);
		$child->page->publish(new PageTransformer);
	}

	/**
	 * @test
	 * @group wip
	 */
	public function publish_CreatesPublishedPageInstance()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$count = PublishedPage::count();

		$route->page->publish(new PageTransformer);
		$this->assertEquals($count + 1, PublishedPage::count());
	}

	/**
	 * @test
	 * @group wip
	 */
	public function publish_AssociatesPublishedPageWithPageInstance()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$count = $route->page->history()->count();

		$route->page->publish(new PageTransformer);
		$this->assertEquals($count + 1, $route->page->history()->count());
		$this->assertNotNull($route->page->published);
	}

	/**
	 * @test
	 * @group wip
	 * @group integration
	 *
	 * This tests integration between $page->publish(), $route->makeActive() and $route->delete().
	 */
	public function publish_WhenThereIsAnActiveRoute_ActiveRouteGetsRedirected()
	{
		$route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
		$route->parent->makeActive();
		$route->makeActive();

		$draft = factory(Route::class)->create([ 'page_id' => $route->page_id, 'parent_id' => $route->parent_id, 'slug' => '/foobar' ]);

		$count = Redirect::count();

		$page = $route->page->fresh();
		$page->publish(new PageTransformer);
		$this->assertEquals($count+1, Redirect::count());
	}

	/**
	 * @test
	 * @group wip
	 */
	public function publish_DraftRouteBecomesActiveRoute()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();

		$page = $route->page->fresh();
		$page->publish(new PageTransformer);

		$route = $route->fresh();
		$this->assertTrue($route->isActive());
		$this->assertEquals($route->getKey(), $route->page->activeRoute->getKey());
	}

	/**
	 * @test
	 * @group wip
	 */
	public function publish_PageOnlyHasOneRoute()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();

		$page = $route->page->fresh();
		$page->publish(new PageTransformer);

		$this->assertCount(1, $page->routes);
	}

	/**
	 * @test
	 * @group wip
	 */
	public function publish_PublishedPageBakeContainsSerializedPageInstance()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$route->page->publish(new PageTransformer);

		$json = fractal($route->page, new PageTransformer)->parseIncludes([ 'blocks', 'canonical' ])->toJson();

		$this->assertEquals($json, $route->published_page->bake);
	}



	/**
	 * @test
	 * @group wip
	 */
	public function revert_WhenPageHasUnsavedChanges_ThrowsException()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$route->page->publish(new PageTransformer);

		$route->page->title = 'Foobar!';

		$this->expectException(Exception::class);
		$route->page->revert($route->page->published);
	}

	/**
	 * @test
	 * @group wip
	 */
	public function revert_WhenPublishedPageIsNotAssociatedWithPage_ThrowsException()
	{
		$r1 = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$r1->page->publish(new PageTransformer);

		$r2 = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$r2->page->publish(new PageTransformer);

		$this->expectException(Exception::class);
		$r1->page->revert($r2->page->published);
	}

	/**
	 * @test
	 * @group wip
	 */
	public function revert_RevertsPageToMatchPublishedPage()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
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
	 * @group wip
	 */
	public function revert_RevertsBlocksToMatchPublishedPage()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$page = $route->page;

		$blocks = factory(Block::class, 2)->create([ 'page_id' => $page->getKey(), 'region_name' => 'test-region' ]);
		$page->publish(new PageTransformer);

		$moreBlocks = factory(Block::class, 3)->create([ 'page_id' => $page->getKey(), 'region_name' => 'test-region' ]);

		$page = $page->fresh();
		$this->assertCount(5, $page->blocks);

		$page->revert($page->published);
		$this->assertCount(2, $page->blocks);
	}



	/**
	 * @test
	 */
	public function clearRegion_DeletesAllBlocksForGivenPageAndRegion()
	{
		$page = factory(Page::class)->create();
		factory(Block::class, 3)->create([ 'page_id' => $page->getKey() ]);

		$page->clearRegion('test-region');
		$this->assertEquals(0, $page->blocks()->count());
	}

	/**
	 * @test
	 */
	public function clearRegion_DoesNotDeleteBlocksInOtherRegions()
	{
		$page = factory(Page::class)->create();

		factory(Block::class, 3)->create([ 'page_id' => $page->getKey() ]);
		factory(Block::class, 2)->create([ 'page_id' => $page->getKey(), 'region_name' => 'foobar' ]);

		$page->clearRegion('foobar');
		$this->assertEquals(3, $page->blocks()->count());
	}



	/**
	 * @test
	 */
	public function getPageDefinition_ReturnLayoutDefinition(){
		$page = factory(Page::class)->make();
		$this->assertInstanceOf(LayoutDefinition::class, $page->getLayoutDefinition());
	}



	/**
	 * @test
	 */
	public function getLayoutDefinition_WhenPageDefinitionIsNotLoaded_LoadsSupportedLayoutDefinition(){
		$page = factory(Page::class)->make();
		$definition = $page->getLayoutDefinition();

		$this->assertNotEmpty($definition);
		$this->assertEquals('test-layout', $definition->name);
	}

	/**
	 * @test
	 */
	public function getLayoutDefinition_WhenLayoutDefinitionIsLoaded_DoesNotReloadLayoutDefinition(){
		$page = factory(Page::class)->make();
		$page->getLayoutDefinition(); 					// This should populate $pageDefinition

		$page = Mockery::mock($page)->makePartial()->shouldAllowMockingProtectedMethods();
		$page->shouldNotReceive('loadLayoutDefinition');

		$definition = $page->getLayoutDefinition(); 	// This should not re-populate $pageDefinition
		$this->assertNotEmpty($definition);				// Is populated, but not empty.
	}

	/**
	 * @test
	 */
	public function getLayoutDefinition_WithRegionDefinitionsWhenLayoutDefinitionIsLoadedWithoutRegions_HasRegionDefinitions()
	{
		$page = factory(Page::class)->make();
		$page->loadLayoutDefinition();

		$definition = $page->getLayoutDefinition(true);

		// Ensure that our assertion does not trigger loading of Region definitions
		$definition = Mockery::mock($definition)->makePartial()->shouldAllowMockingProtectedMethods();
		$definition->shouldNotReceive('loadRegionDefinitions');

		$this->assertCount(1, $definition->getRegionDefinitions());
	}

	/**
	 * @test
	 */
	public function getLayoutDefinition_WithRegionDefinitionsWhenLayoutDefinitionIsLoadedWithRegions_HasRegionDefinitions()
	{
		$page = factory(Page::class)->make();
		$definition = $page->getLayoutDefinition(true);

		// Ensure that our assertion does not trigger loading of Region definitions
		$definition = Mockery::mock($definition)->makePartial()->shouldAllowMockingProtectedMethods();
		$definition->shouldNotReceive('loadRegionDefinitions');

		$this->assertCount(1, $definition->getRegionDefinitions());
	}

}
