<?php
namespace Tests\Unit\Models;

use Exception;
use Tests\TestCase;
use App\Models\PageContent;
use App\Models\Site;
use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RedirectTest extends TestCase
{

	/**
	 * @test
	 * @group integration
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
     */


	/**
	 * @test
	 */
	public function createFromRoute_CreatesRedirectFromRoute()
	{
		$count = Redirect::count();

		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		$redirect = Redirect::createFromRoute($route);

		$this->assertEquals($count+1, Redirect::count());
		$this->assertInstanceOf(Redirect::class, $redirect);
		$this->assertTrue($redirect->wasRecentlyCreated);
	}

	/**
	 * @test
	 */
	public function createFromRoute_RemovesAnyExistingRedirects()
	{
		// Create bystander...
		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		Redirect::createFromRoute($route);

		// Create initial Redirect
		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		$r1 = Redirect::createFromRoute($route);

		$count = Redirect::count();

		// Create a duplicate Redirect
		$r2 = Redirect::createFromRoute($route);

		// Count to ensure we've not lost anything we shouldn't have
		$this->assertEquals($count, Redirect::count());

		// Ensure that $r1 is gone, but $r2 is present
		$this->assertNull(Redirect::find($r1->getKey()));
		$this->assertInstanceOf(Redirect::class, Redirect::find($r2->getKey()));
	}


	/**
	 * @test
	 */
	public function findByPath_WhenPathExists_ReturnsItem()
	{
		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		$redirect = Redirect::createFromRoute($route);

		$result = Redirect::findByPath($redirect->path);
		$this->assertEquals($redirect->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findByPath_WhenPathDoesNotExist_ReturnsNull()
	{
		$this->assertNull(Redirect::findByPath('/foobar'));
	}



	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathExists_ReturnsItem()
	{
		$route = factory(Page::class)->states('withParent', 'withPage')->create();
		$redirect = Redirect::createFromRoute($route);

		$result = Redirect::findByPathOrFail($redirect->path);
		$this->assertEquals($redirect->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathDoesNotExist_ReturnsNull()
	{
        $this->expectException(ModelNotFoundException::class);
		Redirect::findByPathOrFail('/foobar');
	}

}
