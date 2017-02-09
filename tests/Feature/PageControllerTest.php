<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Page;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PageControllerTest extends TestCase
{
	use DatabaseTransactions;

	protected $user;

	protected function setUp()
	{
		parent::setUp();

		$this->user = $this->createUser();
	}

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function not_test_unauthenticated_users_cant_access()
	{
		$no_access = [
			''
		];

		foreach ($no_access as $url)
		{
			$response = $this->json('GET', $url);
			$response->assertStatus(403);
		}
	}

	public function test_creating_a_root_page()
	{
		$page = factory(Page::class)->make();
		$this->actingAs($this->user);

		$page->root = true;
		$page->slug = "test";
		$response = $this->json('POST', '/api/page', $page->toArray());

		$response->assertStatus(200);

		$this->assertDatabaseHas('pages', [
			"title" => $page->title
		]);

		$this->assertDatabaseHas('routes', [
			"slug" => $page->slug
		]);
	}

	public function test_creating_parent_page()
	{
		$parent = factory(Page::class)->make();
		$this->actingAs($this->user);
		$parent->root = true;
		$parent->slug = "test";
		$parent->save();

		$page = factory(Page::class)->make();
		$page->parent = $parent['id'];
		$page->slug = "slllug";

		$arr = $page->toArray();
		$arr['parent'] = $page->parent->id;

			$response = $this->json('POST', '/api/page', $arr);

		$response->assertStatus(200);

		$this->assertDatabaseHas('pages', [
			"title" => $page->title
		]);

		$this->assertDatabaseHas('routes', [
			"slug" => $page->slug
		]);
	}

}
