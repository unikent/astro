<?php
namespace Tests\Unit\Models;

use Exception;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Site;
use App\Models\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RouteTest extends TestCase
{

	/**
	 * @test
	 */
	function scopeActive_ReturnsActiveRoutesOnly()
	{
		$routes = factory(Route::class, 2)->states('withPage', 'withParent')->create()->each(function($r) {
	        $r->makeCanonical();
	    });

		factory(Route::class, 2)->states('withPage', 'withParent')->create();

		$results = Route::canonical()->get()->pluck('id');
		$this->assertContains($routes[0]->getKey(), $results);
		$this->assertContains($routes[1]->getKey(), $results);
	}

	/**
	 * @test
	 */
	function scopeActive_WithFalseArgument_ReturnsInactiveRoutesOnly()
	{
		factory(Route::class, 2)->states('withPage', 'withParent')->create()->each(function($r) {
	        $r->makeCanonical();
	    });

		$routes = factory(Route::class, 2)->states('withPage', 'withParent')->create();

		$results = Route::canonical(false)->get()->pluck('id');
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

		$route->is_canonical = false;
	}

	/**
	 * @test
	 */
	function setIsActiveAttribute_WhenTruthy_ThrowsException()
	{
		$route = factory(Route::class)->make();
		$this->expectException(Exception::class);

		$route->is_canonical = true;
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
	function makeActive_RemovesInactiveRoutesForPage()
	{
		$parents = factory(Route::class, 2)->states('withParent', 'withPage')->create();
		$parents[0]->makeActive(0);

		$routes = factory(Route::class, 3)->create([ 'parent_id' => $parents[0]->getKey(), 'page_id' => $parents[0]->page->getKey() ]);
		$count = $parents[0]->page->routes()->active(false)->count();

		$routes[2]->makeActive();

		// Does not delete inactive Routes on another page
		$this->assertEquals(1, $parents[1]->page->routes()->active(false)->count());

		// Does not delete active Routes on the same page
		$this->assertEquals(2, $parents[0]->page->routes()->active()->count());

		// Does delete other inactive Routes on the same page
		$this->assertEquals(0, $parents[0]->page->routes()->active(false)->count());
	}


	/**
	 * @test
	 */
	function scopeCanonical_ReturnsCanonicalRoutesOnly()
	{
		$routes = factory(Route::class, 2)->states('withPage', 'withParent')->create()->each(function($r) {
	        $r->makeCanonical();
	    });

		factory(Route::class, 2)->states('withPage', 'withParent')->create();

		$results = Route::canonical()->get()->pluck('id');
		$this->assertContains($routes[0]->getKey(), $results);
		$this->assertContains($routes[1]->getKey(), $results);
	}

	/**
	 * @test
	 */
	function scopeCanonical_WithFalseArgument_ReturnsNonCanonicalRoutesOnly()
	{
		factory(Route::class, 2)->states('withPage', 'withParent')->create()->each(function($r) {
	        $r->makeCanonical();
	    });

		$routes = factory(Route::class, 2)->states('withPage', 'withParent')->create();

		$results = Route::canonical(false)->get()->pluck('id');
		$this->assertContains($routes[0]->getKey(), $results);
		$this->assertContains($routes[1]->getKey(), $results);
	}

	/**
	 * @test
	 */
	function setIsCanonicalAttribute_WhenFalsey_ThrowsException()
	{
		$route = factory(Route::class)->make();
		$this->expectException(Exception::class);

		$route->is_canonical = false;
	}

	/**
	 * @test
	 */
	function setIsCanonicalAttribute_WhenTruthy_ThrowsException()
	{
		$route = factory(Route::class)->make();
		$this->expectException(Exception::class);

		$route->is_canonical = true;
	}

	/**
	 * @test
	 */
	function makeCanonical_SetsIsCanonicalToTrue()
	{
		$route = factory(Route::class)->states('withParent', 'withPage')->create();
		$route->makeCanonical();

		$this->assertTrue($route->is_canonical);
	}

	/**
	 * @test
	 */
	function makeCanonical_SetsIsCanonicalToFalseOnOtherRoutesToPage()
	{
		$parent = factory(Route::class)->states('withParent', 'withPage')->create();

		$routes = factory(Route::class, 3)->create([ 'parent_id' => $parent->getKey(), 'page_id' => $parent->page->getKey() ])
			->each(function($r){ $r->makeCanonical(); })
		;

		$routes[1]->makeCanonical();

		foreach($routes as $key => $route){ // For some reason, pass-by-reference isn't working here...
			$routes[$key] = $route->fresh();
		}

		$this->assertFalse($routes[0]->is_canonical);
		$this->assertTrue($routes[1]->is_canonical);
		$this->assertFalse($routes[2]->is_canonical);
	}

	/**
	 * @test
	 */
	public function isCanonical_WhenIsCanonicalIsTrue_ReturnsTrue()
	{
		$route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
		$route->makeCanonical();

		$this->assertTrue($route->isCanonical());
	}

	/**
	 * @test
	 */
	public function isCanonical_WhenIsCanonicalIsFalse_ReturnsFalse()
	{
		$route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
		$this->assertFalse($route->isCanonical());
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
		$r2 = factory(Route::class)->create([ 'page_id' => $r1->page_id, 'parent_id' => $r1->parent_id ]);

		$site = factory(Site::class)->states('withPublishingGroup')->create();
		$r2->makeSite($site);

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
		$page = factory(Page::class)->create();

		$r1 = factory(Route::class)->states('isRoot')->create([ 'page_id' => $page->getKey() ]);
		$r2 = factory(Route::class)->create([ 'page_id' => $page->getKey(), 'parent_id' => $r1->getKey() ]);
		$r3 = factory(Route::class)->make([ 'page_id' => $page->getKey(), 'parent_id' => $r2->getKey() ]);

		$path = $r3->generatePath();
		$this->assertEquals('/' . $r2->slug . '/' . $r3->slug, $path); // $r1 is a root node, so has no slug
	}



	/**
	 * @test
	 */
	function whenSaving_GeneratesPath()
	{
		$page = factory(Page::class)->create();

		$r1 = factory(Route::class)->states('isRoot')->create([ 'page_id' => $page->getKey() ]);
		$r2 = factory(Route::class)->create([ 'page_id' => $page->getKey(), 'parent_id' => $r1->getKey() ]);
		$r3 = factory(Route::class)->make([ 'page_id' => $page->getKey(), 'parent_id' => $r2->getKey() ]);

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

}
