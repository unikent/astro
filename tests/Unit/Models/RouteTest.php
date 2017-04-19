<?php
namespace Tests\Unit\Models;

use Exception;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Route;

class RouteTest extends TestCase
{

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

		$routes = factory(Route::class, 3)->create([
			'is_canonical' => true,
			'parent_id' => $parent->getKey(),
			'page_id' => $parent->page->getKey(),
		]);

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

}
