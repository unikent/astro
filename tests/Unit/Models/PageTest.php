<?php
namespace Tests\Unit\Models;

use Mockery;
use Exception;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Block;
use App\Models\Route;
use App\Models\PublishedPage;
use App\Http\Transformers\Api\v1\PageTransformer;
use App\Models\Definitions\Layout as LayoutDefinition;

class PageTest extends TestCase
{

	/**
	 * @test
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
	 */
	public function publish_RouteIsMadeActive()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$this->assertFalse($route->isActive());

		$route->page->publish(new PageTransformer);

		$route = $route->fresh();
		$this->assertTrue($route->isActive());
	}

	/**
	 * @test
	 */
	public function publish_RouteIsMadeCanonical()
	{
		$route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
		$this->assertFalse($route->isCanonical());

		$route->page->publish(new PageTransformer);

		$route = $route->fresh();
		$this->assertTrue($route->isCanonical());
	}

	/**
	 * @test
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
