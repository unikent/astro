<?php
namespace Tests\Unit\Models;

use Mockery;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Block;
use App\Models\Definitions\Layout as LayoutDefinition;

class PageTest extends TestCase
{

	/**
	 * @test
	 */
	public function scopeSites_ReturnsPagesWithIsSiteSetToTrue()
	{
		$pages = factory(Page::class, 3)->create();
		$sites = factory(Page::class, 2)->create([ 'is_site' => true ]);

		$results = Page::sites()->get();
		$this->assertCount(2, $results);

		$ids = $results->pluck('id');
		$this->assertContains($sites[0]->getKey(), $ids);
		$this->assertContains($sites[1]->getKey(), $ids);
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



	/**
	 * @test
	 */
	public function toArray_WhenLayoutDefinitionIsNotLoaded_DoesNotIncludeLayoutDefinition()
	{
		$page = factory(Page::class)->make();

		$output = $page->toArray();
		$this->assertArrayNotHasKey('layoutDefinition', $output);
	}

	/**
	 * @test
	 */
	public function toArray_WhenLayoutDefinitionIsLoaded_IncludesLayoutDefinition()
	{
		$page = factory(Page::class)->make();
		$page->loadLayoutDefinition();

		$output = $page->toArray();
		$this->assertArrayHasKey('layoutDefinition', $output);
		$this->assertNotEmpty($output['layoutDefinition']);
	}

}
