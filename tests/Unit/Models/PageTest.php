<?php
namespace Tests\Unit\Models;

use Exception;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Redirect;
use App\Http\Transformers\Api\v1\PageContentTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PageTest extends TestCase
{

	/**
	 * @test
	 * @group integration
	 */
	public function pageRelation_WhenPageIsSoftDeleted_ReturnsPage()
	{
		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		$redirect = Redirect::createFromRoute($route);

		$page = $route->page;

		$page->delete();
		$redirect = $redirect->fresh();

		$this->assertTrue($page->trashed());
		$this->assertEquals($page->getKey(), $redirect->page->getKey());
	}

	/**
	 * @test
	 */
	function scopeActive_ReturnsActiveRoutesOnly()
	{
		$routes = factory(Page::class, 2)->states('withPage', 'withParent')->create()->each(function($model){
			$model->makeActive();
		});

		factory(Page::class, 2)->states('withPage', 'withParent')->create();

		$results = Page::active()->get()->pluck('id');
		$this->assertContains($routes[0]->getKey(), $results);
		$this->assertContains($routes[1]->getKey(), $results);
	}

	/**
	 * @test
	 */
	function scopeActive_WithFalseArgument_ReturnsInactiveRoutesOnly()
	{
		factory(Page::class, 2)->states('withPage', 'withParent')->create();

		$routes = factory(Page::class, 2)->states('withPage', 'withParent')->create();

		$results = Page::active(false)->get()->pluck('id');
		$this->assertContains($routes[0]->getKey(), $results);
		$this->assertContains($routes[1]->getKey(), $results);
	}


	/**
	 * @test
	 */
	function setIsActiveAttribute_WhenFalsey_ThrowsException()
	{
		$route = factory(Page::class)->make();
		$this->expectException(Exception::class);

		$route->is_active = false;
	}

	/**
	 * @test
	 */
	function setIsActiveAttribute_WhenTruthy_ThrowsException()
	{
		$route = factory(Page::class)->make();
		$this->expectException(Exception::class);

		$route->is_active = true;
	}

	/**
	 * @test
	 */
	public function isActive_WhenIsActiveIsTrue_ReturnsTrue()
	{
		$route = factory(Page::class)->states([ 'withPage', 'withParent' ])->create();
		$route->makeActive();

		$this->assertTrue($route->isActive());
	}

	/**
	 * @test
	 */
	public function isActive_WhenIsActiveIsFalse_ReturnsFalse()
	{
		$route = factory(Page::class)->states([ 'withPage', 'withParent' ])->create();
		$this->assertFalse($route->isActive());
	}

	/**
	 * @test
	 */
	function makeActive_SetsIsActiveToTrue()
	{
		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		$route->makeActive();

		$this->assertTrue($route->is_active);
	}


	/**
	 * @test
	 */
	public function isSite_WhenSiteIdIsSet_ReturnsTrue()
	{
		$route = factory(Page::class)->states('withPage', 'withParent', 'withSite')->create();
		$this->assertTrue($route->isSite());
	}

	/**
	 * @test
	 */
	public function isSite_WhenSiteIdNotSet_ReturnsFalse()
	{
		$route = factory(Page::class)->states('withPage', 'withParent')->create();
		$this->assertFalse($route->isSite());
	}



	/**
	 * @test
	 */
	function generatePath_WhenNoParentOrSlugIsSet_SetsPathToRoot()
	{
		$route = factory(Page::class)->states('withPage')->make([ 'slug' => null ]);
		$path = $route->generatePath();

		$this->assertEquals('/', $path);
	}

	/**
	 * @test
	 */
	function generatePath_WhenNoParentButSlugIsSet_ThrowsException()
	{
		$route = factory(Page::class)->states('withPage')->make();

		$this->expectException(Exception::class);
		$route->generatePath();
	}

	/**
	 * @test
	 */
	function generatePath_WhenHasParents_SetsPathUsingParentSlugs()
	{
		$r1 = factory(Page::class)->states('isRoot', 'withPage')->create();
		$r2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $r1->getKey() ]);
		$r3 = factory(Page::class)->states('withPage')->make([ 'parent_id' => $r2->getKey() ]);

		$path = $r3->generatePath();
		$this->assertEquals('/' . $r2->slug . '/' . $r3->slug, $path); // $r1 is a root node, so has no slug
	}



	/**
	 * @test
	 */
	function whenSaving_GeneratesPath()
	{
		$r1 = factory(Page::class)->states('isRoot', 'withPage')->create();
		$r2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $r1->getKey() ]);
		$r3 = factory(Page::class)->states('withPage')->make([ 'parent_id' => $r2->getKey() ]);

		$r3->save();
		$this->assertEquals('/' . $r2->slug . '/' . $r3->slug, $r3->path); // $r1 is a root node, so has no slug
	}



	/**
	 * @test
	 */
	public function findByPath_WhenPathExists_ReturnsItem()
	{
		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		$result = Page::findByPath($route->path);
		$this->assertEquals($route->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findByPath_WhenPathDoesNotExist_ReturnsNull()
	{
		$this->assertNull(Page::findByPath('/foobar'));
	}



	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathExists_ReturnsItem()
	{
		$route = factory(Page::class)->states('withParent', 'withPage')->create();

		$result = Page::findByPathOrFail($route->path);
		$this->assertEquals($route->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathDoesNotExist_ReturnsNull()
	{
        $this->expectException(ModelNotFoundException::class);
		Page::findByPathOrFail('/foobar');
	}



	/**
	 * @test
	 */
	public function save_WhenRouteIsNotActive_SavesNewDraftRoute()
	{
		$count = Page::count();

		$route = factory(Page::class)->states('isRoot', 'withPage')->make();
		$route->save();

		$this->assertEquals($count + 1, Page::count());

		$route = $route->fresh();
		$this->assertFalse($route->isActive());
	}

	/**
	 * @test
	 */
	public function save_WhenRouteIsNotActive_DeletesAnyOtherInactiveRoutes()
	{
		$count = Page::count();

		$r1 = factory(Page::class)->states('isRoot', 'withPage')->make();
		$r1->save();

		$this->assertEquals($count+1, Page::count());

		$r2 = $r1->replicate();
		$r2->save();

		$this->assertEquals($count+1, Page::count()); 						// Number of routes is the same...
		$this->assertNull(Page::find($r1->getKey())); 						// ...but $r1 is gone...
		$this->assertInstanceOf(Page::class, Page::find($r2->getKey())); 	// ...and $r2 is present.
	}

	/**
	 * @test
	 *
	 * A Route should't really be updated like this when running in a production
	 * environment. However, we want to make sure that save still behaves normally.
	 */
	public function save_WhenRouteIsActive_UpdatesRoute()
	{
		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		$route->makeActive();

		$count = Page::count();

		$route->slug = 'foobar123';
		$route->save();

		$this->assertEquals($count, Page::count()); // Should not have created a new Route
		$this->assertInstanceOf(Page::class, Page::find($route->getKey()));

		$route = $route->fresh();
		$this->assertEquals('foobar123', $route->slug);
	}

	/**
	 * @test
	 *
	 * environment. However, we want to make sure that save still behaves normally.
	 */
	public function save_WhenSuccessful_ReturnsTrue()
	{
		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		$route->makeActive();

		$count = Page::count();

		$route->slug = 'foobar123';
		$this->assertTrue($route->save());
	}



	/**
	 * @test
	 */
	public function cloneDescendants_WhenAllDescendantsArePublished_ClonesAllDescendantsAsInactive()
	{
		$a1 = factory(Page::class)->states('withPublishedParent', 'withPage')->create();
		$a1->page->publish(new PageContentTransformer);

		$a2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $a1->getKey() ]);
		$a2->page->publish(new PageContentTransformer);

		$a3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);
		$a3->page->publish(new PageContentTransformer);

		$a4 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);
		$a4->page->publish(new PageContentTransformer);

		$b1 = factory(Page::class)->states('withPublishedParent', 'withPage')->create();

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
	 *
	 * This test depends upon the $route->save() function working properly. Its worth
	 * keeping as an integration tests to prevent against regressions in this behaviour.
	 */
	public function cloneDescendants_WhenSomeDescendantsAreDraft_ClonesAllDescendantsAndRemovesOriginalDrafts()
	{
		$a1 = factory(Page::class)->states('withPublishedParent', 'withPage')->create();
		$a1->page->publish(new PageContentTransformer);

		$a2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $a1->getKey() ]);
		$a2->page->publish(new PageContentTransformer);

		$a3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);
		$a4 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);

		$b1 = factory(Page::class)->states('withPublishedParent', 'withPage')->create();

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
	 */
	public function cloneDescendants_WhenDestinationHasOwnDescendants_RetainsOriginalDescendants()
	{
		// Original Route, with descendants
		$a1 = factory(Page::class)->states('withPublishedParent', 'withPage')->create();
		$a1->page->publish(new PageContentTransformer);

		$a2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $a1->getKey() ]);
		$a2->page->publish(new PageContentTransformer);

		$a3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);
		$a4 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);

		// New Route, with own descendants
		$b1 = factory(Page::class)->states('withPublishedParent', 'withPage')->create();
		$b1->page->publish(new PageContentTransformer);

		$b2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $b1->getKey() ]);
		$b2->page->publish(new PageContentTransformer);

		$b3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $b1->getKey() ]);

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
	public function delete_WhenRouteIsNotActive_DeletesRoute()
	{
		$route = factory(Page::class)->states('withPage', 'withParent')->create();
		$route->delete();

		$this->assertNull(Page::find($route->getKey()));
	}

	/**
	 * @test
	 */
	public function delete_WhenRouteIsNotActive_DoesNotCreateRedirect()
	{
		$count = Redirect::count();

		$route = factory(Page::class)->states('withPage', 'withParent')->create();
		$route->delete();

		$this->assertEquals($count, Redirect::count());
	}

	/**
	 * @test
	 */
	public function delete_WhenRouteIsActive_DeletesRoute()
	{
		$route = factory(Page::class)->states('withPage', 'withParent')->create();
		$route->makeActive();
		$route->delete();

		$this->assertNull(Page::find($route->getKey()));
	}

	/**
	 * @test
	 */
	public function delete_WhenRouteIsActive_CreatesNewRedirect()
	{
		$count = Redirect::count();

		$route = factory(Page::class)->states('withPage', 'withParent')->create();
		$route->makeActive();
		$route->delete();

		$this->assertEquals($count + 1, Redirect::count());

		$redirect = Redirect::all()->last();
		$this->assertEquals($route->path, $redirect->path);
		$this->assertEquals($route->page_id, $redirect->page_id);
	}

}
