<?php
namespace Tests\Unit\Models;

use Exception;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Site;
use App\Models\Route;
use App\Models\Redirect;
use App\Http\Transformers\Api\v1\PageTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RouteTest extends TestCase
{

	/**
	 * @test
	 */
	function scopeActive_ReturnsActiveRoutesOnly()
	{
		$routes = factory(Route::class, 2)->states('withPage', 'withParent')->create()->each(function($model){
			$model->makeActive();
		});

		factory(Route::class, 2)->states('withPage', 'withParent')->create();

		$results = Route::active()->get()->pluck('id');
		$this->assertContains($routes[0]->getKey(), $results);
		$this->assertContains($routes[1]->getKey(), $results);
	}

	/**
	 * @test
	 */
	function scopeActive_WithFalseArgument_ReturnsInactiveRoutesOnly()
	{
		factory(Route::class, 2)->states('withPage', 'withParent')->create();

		$routes = factory(Route::class, 2)->states('withPage', 'withParent')->create();

		$results = Route::active(false)->get()->pluck('id');
		$this->assertContains($routes[0]->getKey(), $results);
		$this->assertContains($routes[1]->getKey(), $results);
	}


	/**
	 * @test
	 */
	function setIsActiveAttribute_WhenFalsey_ThrowsException()
	{
		$route = factory(Route::class)->make();
		$this->expectException(Exception::class);

		$route->is_active = false;
	}

	/**
	 * @test
	 */
	function setIsActiveAttribute_WhenTruthy_ThrowsException()
	{
		$route = factory(Route::class)->make();
		$this->expectException(Exception::class);

		$route->is_active = true;
	}

	/**
	 * @test
	 */
	public function isActive_WhenIsActiveIsTrue_ReturnsTrue()
	{
		$route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
		$route->makeActive();

		$this->assertTrue($route->isActive());
	}

	/**
	 * @test
	 */
	public function isActive_WhenIsActiveIsFalse_ReturnsFalse()
	{
		$route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
		$this->assertFalse($route->isActive());
	}

	/**
	 * @test
	 */
	function makeActive_SetsIsActiveToTrue()
	{
		$route = factory(Route::class)->states('withParent', 'withPage')->create();
		$route->makeActive();

		$this->assertTrue($route->is_active);
	}

	/**
	 * @test
	 */
	function makeActive_RemovesAllOtherRoutesForPage()
	{
		$active = factory(Route::class)->states('withParent', 'withPage')->create();
		$active->makeActive();

		$page = $active->page;

		$draft = factory(Route::class)->create([ 'parent_id' => $active->parent_id, 'page_id' => $page->getKey() ]);

		$page = $page->fresh();
		$this->assertCount(2, $page->routes);

		$draft->makeActive();

		$page = $page->fresh();
		$this->assertCount(1, $page->routes);

		$this->assertEquals($draft->getKey(), $page->activeRoute->getKey());
	}



	/**
	 * @test
	 */
	function makeSite_WithSiteInstance_SetsSiteIdOnCurrentRoute()
	{
		$route = factory(Route::class)->states('withPage', 'withParent')->create();

		$site = factory(Site::class)->states('withPublishingGroup')->create();
		$route->makeSite($site);

		$route = $route->fresh();
		$this->assertEquals($route->site_id, $site->getKey());
	}

	/**
	 * @test
	 */
	function makeSite_WithSiteInstance_SetsSiteIdOnAllOtherRoutesToPage()
	{
		$r1 = factory(Route::class)->states('withPage', 'withParent')->create();
		$r1->makeActive();

		$r2 = factory(Route::class)->create([ 'page_id' => $r1->page_id, 'parent_id' => $r1->parent_id ]);

		$site = factory(Site::class)->states('withPublishingGroup')->create();
		$r1->makeSite($site);

		$r1 = $r1->fresh();
		$this->assertEquals($r1->site_id, $site->getKey());

		$r2 = $r2->fresh();
		$this->assertEquals($r2->site_id, $site->getKey());
	}

	/**
	 * @test
	 */
	function makeSite_WithSiteId_SetsSiteIdOnCurrentRoute()
	{
		$route = factory(Route::class)->states('withPage', 'withParent')->create();

		$site = factory(Site::class)->states('withPublishingGroup')->create();
		$route->makeSite($site->getKey());

		$route = $route->fresh();
		$this->assertEquals($route->site_id, $site->getKey());
	}

	/**
	 * @test
	 */
	function makeSite_WithSiteId_SetsSiteIdOnAllOtherRoutesToPage()
	{
		$r1 = factory(Route::class)->states('withPage', 'withParent')->create();
		$r1->makeActive();

		$r2 = factory(Route::class)->create([ 'page_id' => $r1->page_id, 'parent_id' => $r1->parent_id ]);

		$site = factory(Site::class)->states('withPublishingGroup')->create();
		$r2->makeSite($site->getKey());

		$r1 = $r1->fresh();
		$this->assertEquals($r1->site_id, $site->getKey());

		$r2 = $r2->fresh();
		$this->assertEquals($r2->site_id, $site->getKey());
	}



	/**
	 * @test
	 */
	public function isSite_WhenSiteIdIsSet_ReturnsTrue()
	{
		$route = factory(Route::class)->states('withPage', 'withParent', 'withSite')->create();
		$this->assertTrue($route->isSite());
	}

	/**
	 * @test
	 */
	public function isSite_WhenSiteIdNotSet_ReturnsFalse()
	{
		$route = factory(Route::class)->states('withPage', 'withParent')->create();
		$this->assertFalse($route->isSite());
	}



	/**
	 * @test
	 */
	function generatePath_WhenNoParentOrSlugIsSet_SetsPathToRoot()
	{
		$route = factory(Route::class)->states('withPage')->make([ 'slug' => null ]);
		$path = $route->generatePath();

		$this->assertEquals('/', $path);
	}

	/**
	 * @test
	 */
	function generatePath_WhenNoParentButSlugIsSet_ThrowsException()
	{
		$route = factory(Route::class)->states('withPage')->make();

		$this->expectException(Exception::class);
		$route->generatePath();
	}

	/**
	 * @test
	 */
	function generatePath_WhenHasParents_SetsPathUsingParentSlugs()
	{
		$r1 = factory(Route::class)->states('isRoot', 'withPage')->create();
		$r2 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $r1->getKey() ]);
		$r3 = factory(Route::class)->states('withPage')->make([ 'parent_id' => $r2->getKey() ]);

		$path = $r3->generatePath();
		$this->assertEquals('/' . $r2->slug . '/' . $r3->slug, $path); // $r1 is a root node, so has no slug
	}



	/**
	 * @test
	 */
	function whenSaving_GeneratesPath()
	{
		$r1 = factory(Route::class)->states('isRoot', 'withPage')->create();
		$r2 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $r1->getKey() ]);
		$r3 = factory(Route::class)->states('withPage')->make([ 'parent_id' => $r2->getKey() ]);

		$r3->save();
		$this->assertEquals('/' . $r2->slug . '/' . $r3->slug, $r3->path); // $r1 is a root node, so has no slug
	}



	/**
	 * @test
	 */
	public function findByPath_WhenPathExists_ReturnsItem()
	{
		$route = factory(Route::class)->states('withParent', 'withPage')->create();
		$result = Route::findByPath($route->path);
		$this->assertEquals($route->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findByPath_WhenPathDoesNotExist_ReturnsNull()
	{
		$this->assertNull(Route::findByPath('/foobar'));
	}



	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathExists_ReturnsItem()
	{
		$route = factory(Route::class)->states('withParent', 'withPage')->create();

		$result = Route::findByPathOrFail($route->path);
		$this->assertEquals($route->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathDoesNotExist_ReturnsNull()
	{
        $this->expectException(ModelNotFoundException::class);
		Route::findByPathOrFail('/foobar');
	}



	/**
	 * @test
	 */
	public function save_WhenRouteIsNotActive_SavesNewDraftRoute()
	{
		$count = Route::count();

		$route = factory(Route::class)->states('isRoot', 'withPage')->make();
		$route->save();

		$this->assertEquals($count + 1, Route::count());

		$route = $route->fresh();
		$this->assertFalse($route->isActive());
	}

	/**
	 * @test
	 */
	public function save_WhenRouteIsNotActive_DeletesAnyOtherInactiveRoutes()
	{
		$count = Route::count();

		$r1 = factory(Route::class)->states('isRoot', 'withPage')->make();
		$r1->save();

		$this->assertEquals($count+1, Route::count());

		$r2 = $r1->replicate();
		$r2->save();

		$this->assertEquals($count+1, Route::count()); 						// Number of routes is the same...
		$this->assertNull(Route::find($r1->getKey())); 						// ...but $r1 is gone...
		$this->assertInstanceOf(Route::class, Route::find($r2->getKey())); 	// ...and $r2 is present.
	}

	/**
	 * @test
	 *
	 * A Route should't really be updated like this when running in a production
	 * environment. However, we want to make sure that save still behaves normally.
	 */
	public function save_WhenRouteIsActive_UpdatesRoute()
	{
		$route = factory(Route::class)->states('withParent', 'withPage')->create();
		$route->makeActive();

		$count = Route::count();

		$route->slug = 'foobar123';
		$route->save();

		$this->assertEquals($count, Route::count()); // Should not have created a new Route
		$this->assertInstanceOf(Route::class, Route::find($route->getKey()));

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
		$route = factory(Route::class)->states('withParent', 'withPage')->create();
		$route->makeActive();

		$count = Route::count();

		$route->slug = 'foobar123';
		$this->assertTrue($route->save());
	}



	/**
	 * @test
	 */
	public function cloneDescendants_WhenAllDescendantsArePublished_ClonesAllDescendantsAsInactive()
	{
		$a1 = factory(Route::class)->states('withPublishedParent', 'withPage')->create();
		$a1->page->publish(new PageTransformer);

		$a2 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $a1->getKey() ]);
		$a2->page->publish(new PageTransformer);

		$a3 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);
		$a3->page->publish(new PageTransformer);

		$a4 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);
		$a4->page->publish(new PageTransformer);

		$b1 = factory(Route::class)->states('withPublishedParent', 'withPage')->create();

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
		$a1 = factory(Route::class)->states('withPublishedParent', 'withPage')->create();
		$a1->page->publish(new PageTransformer);

		$a2 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $a1->getKey() ]);
		$a2->page->publish(new PageTransformer);

		$a3 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);
		$a4 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);

		$b1 = factory(Route::class)->states('withPublishedParent', 'withPage')->create();

		$a1 = $a1->fresh();
		$count = $a1->descendants()->count();

		$descendants = $b1->cloneDescendants($a1);
		$this->assertEquals($count, $descendants->count());

		$a1 = $a1->fresh();
		$this->assertEquals($count-2, $a1->descendants()->count());

		$this->assertInstanceOf(Route::class, Route::find($a2->getKey()));
		$this->assertNull(Route::find($a3->getKey()));
		$this->assertNull(Route::find($a4->getKey()));
	}

	/**
	 * @test
	 */
	public function cloneDescendants_WhenDestinationHasOwnDescendants_RetainsOriginalDescendants()
	{
		// Original Route, with descendants
		$a1 = factory(Route::class)->states('withPublishedParent', 'withPage')->create();
		$a1->page->publish(new PageTransformer);

		$a2 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $a1->getKey() ]);
		$a2->page->publish(new PageTransformer);

		$a3 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);
		$a4 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $a2->getKey() ]);

		// New Route, with own descendants
		$b1 = factory(Route::class)->states('withPublishedParent', 'withPage')->create();
		$b1->page->publish(new PageTransformer);

		$b2 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $b1->getKey() ]);
		$b2->page->publish(new PageTransformer);

		$b3 = factory(Route::class)->states('withPage')->create([ 'parent_id' => $b1->getKey() ]);

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
		$route = factory(Route::class)->states('withPage', 'withParent')->create();
		$route->delete();

		$this->assertNull(Route::find($route->getKey()));
	}

	/**
	 * @test
	 */
	public function delete_WhenRouteIsNotActive_DoesNotCreateRedirect()
	{
		$count = Redirect::count();

		$route = factory(Route::class)->states('withPage', 'withParent')->create();
		$route->delete();

		$this->assertEquals($count, Redirect::count());
	}

	/**
	 * @test
	 */
	public function delete_WhenRouteIsActive_DeletesRoute()
	{
		$route = factory(Route::class)->states('withPage', 'withParent')->create();
		$route->makeActive();
		$route->delete();

		$this->assertNull(Route::find($route->getKey()));
	}

	/**
	 * @test
	 */
	public function delete_WhenRouteIsActive_CreatesNewRedirect()
	{
		$count = Redirect::count();

		$route = factory(Route::class)->states('withPage', 'withParent')->create();
		$route->makeActive();
		$route->delete();

		$this->assertEquals($count + 1, Redirect::count());

		$redirect = Redirect::all()->last();
		$this->assertEquals($route->path, $redirect->path);
		$this->assertEquals($route->page_id, $redirect->page_id);
	}

}
