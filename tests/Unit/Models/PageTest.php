<?php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Page;
use App\Models\Block;

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

}
