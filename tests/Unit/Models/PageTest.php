<?php
namespace Tests\Unit\Models;

use Exception;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Redirect;
use App\Http\Transformers\Api\v1\PageTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PageTest extends TestCase
{



	/**
	 * @test
	 */
	function scopeActive_ReturnsActiveRoutesOnly()
	{
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 */
	function scopeDraft_WithFalseArgument_ReturnsInactiveRoutesOnly()
	{
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 */
	function generatePath_WhenNoParentOrSlugIsSet_SetsPathToRoot()
	{
		$route = factory(Page::class)->states('withRevision')->make([ 'slug' => null ]);
		$path = $route->generatePath();

		$this->assertEquals('/', $path);
	}

	/**
	 * @test
	 */
	function generatePath_WhenNoParentButSlugIsSet_ThrowsException()
	{
		$route = factory(Page::class)->states('withRevision')->make(['slug' => 'foo']);

		$this->expectException(Exception::class);
		$route->generatePath();
	}

	/**
	 * @test
	 */
	function generatePath_WhenHasParents_SetsPathUsingParentSlugs()
	{
		$r1 = factory(Page::class)->states('withRevision')->create();
		$r2 = factory(Page::class)->states('withRevision')->create([ 'slug' => 'foo', 'parent_id' => $r1->getKey() , 'site_id' => $r1->site_id]);
		$r3 = factory(Page::class)->states('withRevision')->make([ 'slug' => 'bar', 'parent_id' => $r2->getKey() , 'site_id' => $r1->site_id]);

		$path = $r3->generatePath();
		$this->assertEquals('/' . $r2->slug . '/' . $r3->slug, $path); // $r1 is a root node, so has no slug
	}



	/**
	 * @test
	 */
	function whenSaving_GeneratesPath()
	{
		$r1 = factory(Page::class)->states( 'withRevision')->create();
		$r2 = factory(Page::class)->states('withRevision')->create([ 'slug' => 'foo', 'parent_id' => $r1->getKey(), 'site_id' => $r1->site_id ]);
		$r3 = factory(Page::class)->states('withRevision')->make([ 'slug' => 'bar', 'parent_id' => $r2->getKey(), 'site_id' => $r1->site_id ]);

		$r3->save();
		$this->assertEquals('/' . $r2->slug . '/' . $r3->slug, $r3->path); // $r1 is a root node, so has no slug
	}



	/**
	 * @test
	 */
	public function findByPath_WhenPathExists_ReturnsItem()
	{
        return $this->markTestIncomplete();
		$route = factory(Page::class)->states('withParent', 'withRevision')->create();
		$result = Page::findByPath($route->path);
		$this->assertEquals($route->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findBySiteAndPath_WhenPathDoesNotExist_ReturnsNull()
	{
	    return $this->markTestIncomplete();
		$this->assertNull(Page::findBySiteAndPath(999,'/foobar'));
	}



	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathExists_ReturnsItem()
	{
        return $this->markTestIncomplete();
		$route = factory(Page::class)->states('withParent', 'withRevision')->create();

		$result = Page::findByPathOrFail($route->path);
		$this->assertEquals($route->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathDoesNotExist_ReturnsNull()
	{
        return $this->markTestIncomplete();
        $this->expectException(ModelNotFoundException::class);
		Page::findByPathOrFail('/foobar');
	}




	/**
	 * @test
     * @group ignore
	 */
	public function cloneDescendants_WhenAllDescendantsArePublished_ClonesAllDescendantsAsInactive()
	{
	    return $this->markTestIncomplete();
		$a1 = factory(Page::class)->states('withPublishedParent', 'withRevision')->create();
		$a1->page->publish(new PageTransformer);

		$a2 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $a1->getKey() ]);
		$a2->page->publish(new PageTransformer);

		$a3 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $a2->getKey() ]);
		$a3->page->publish(new PageTransformer);

		$a4 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $a2->getKey() ]);
		$a4->page->publish(new PageTransformer);

		$b1 = factory(Page::class)->states('withPublishedParent', 'withRevision')->create();

		$a1 = $a1->fresh();
		$count = $a1->descendants()->count();

		$descendants = $b1->cloneDescendants($a1);

		$this->assertEquals($count, $descendants->count());

		$descendant_page_ids = $descendants->pluck('page_id');
		$this->assertContains($a2->page_id, $descendant_page_ids);
		$this->assertContains($a3->page_id, $descendant_page_ids);
		$this->assertContains($a4->page_id, $descendant_page_ids);

		$this->assertNotContains(true, $descendants->pluck('is_active'));
	}

	/**
	 * @test
	 * @group integration
     * @group ignore
	 *
	 * This test depends upon the $route->save() function working properly. Its worth
	 * keeping as an integration tests to prevent against regressions in this behaviour.
	 */
	public function cloneDescendants_WhenSomeDescendantsAreDraft_ClonesAllDescendantsAndRemovesOriginalDrafts()
	{
        return $this->markTestIncomplete();
		$a1 = factory(Page::class)->states('withPublishedParent', 'withRevision')->create();
		$a1->page->publish(new PageTransformer);

		$a2 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $a1->getKey() ]);
		$a2->page->publish(new PageTransformer);

		$a3 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $a2->getKey() ]);
		$a4 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $a2->getKey() ]);

		$b1 = factory(Page::class)->states('withPublishedParent', 'withRevision')->create();

		$a1 = $a1->fresh();
		$count = $a1->descendants()->count();

		$descendants = $b1->cloneDescendants($a1);
		$this->assertEquals($count, $descendants->count());

		$a1 = $a1->fresh();
		$this->assertEquals($count-2, $a1->descendants()->count());

		$this->assertInstanceOf(Page::class, Page::find($a2->getKey()));
		$this->assertNull(Page::find($a3->getKey()));
		$this->assertNull(Page::find($a4->getKey()));
	}

	/**
	 * @test
     * @group ignore
     *
     */
	public function cloneDescendants_WhenDestinationHasOwnDescendants_RetainsOriginalDescendants()
	{
        return $this->markTestIncomplete();
		// Original Route, with descendants
		$a1 = factory(Page::class)->states('withPublishedParent', 'withRevision')->create();
		$a1->page->publish(new PageTransformer);

		$a2 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $a1->getKey() ]);
		$a2->page->publish(new PageTransformer);

		$a3 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $a2->getKey() ]);
		$a4 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $a2->getKey() ]);

		// New Route, with own descendants
		$b1 = factory(Page::class)->states('withPublishedParent', 'withRevision')->create();
		$b1->page->publish(new PageTransformer);

		$b2 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $b1->getKey() ]);
		$b2->page->publish(new PageTransformer);

		$b3 = factory(Page::class)->states('withRevision')->create([ 'parent_id' => $b1->getKey() ]);

		// Perform test
		$a1 = $a1->fresh();
		$count = $a1->descendants()->count();

		$descendants = $b1->cloneDescendants($a1);

		$this->assertEquals($count + 2, $descendants->count()); // Own descendants, plus cloned descendants.

		$descendant_page_ids = $descendants->pluck('page_id');
		$this->assertContains($a2->page_id, $descendant_page_ids);
		$this->assertContains($b2->page_id, $descendant_page_ids);
		$this->assertContains($a3->page_id, $descendant_page_ids);
		$this->assertContains($b3->page_id, $descendant_page_ids);
		$this->assertContains($a4->page_id, $descendant_page_ids);
	}


	/**
	 * @test
	 */
	public function delete_WhenRouteIsNotActive_DoesNotCreateRedirect()
	{
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );	}

	/**
	 * @test
	 */
	public function delete_WhenRouteIsActive_DeletesRoute()
	{
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

	/**
	 * @test
	 */
	public function delete_WhenRouteIsActive_CreatesNewRedirect()
	{
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}

}
