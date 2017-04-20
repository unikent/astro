<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use App\Models\Page;
use App\Models\Block;
use App\Models\Route;
use App\Http\Controllers\Api\v1\PageController;

class PageControllerTest extends ApiControllerTestCase {

    protected function getAttrs($merge = [])
    {
        $page = factory(Page::class)->make();
        $route = factory(Route::class)->states('withParent')->make([ 'page_id' => $page->getKey() ]);

        $block = factory(Block::class)->make();

        $attrs = attrs_for($page) + [
            'route' => attrs_for($route),

            'regions' => [
                'test-region' => [
                    0 => attrs_for($block),
                ],
            ],
        ];

        return array_merge($attrs, $merge);
    }


    /**
     * @test
     * @group authentication
     */
    public function store_WhenUnauthenticated_Returns401(){
        $response = $this->action('POST', PageController::class . '@store', $this->getAttrs());
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthenticated_ChecksAuthorization(){
        Gate::shouldReceive('allows')->with('create', Page::class)->once();

        $this->authenticated();
        $response = $this->action('POST', PageController::class . '@store', $this->getAttrs());
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('POST', PageController::class . '@store', $this->getAttrs());
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function store_WhenAuthorized_CreatesPage(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', $attrs);

        $page = Page::all()->last();
        $this->assertEquals($attrs['title'], $page->title);
    }

    /**
     * @test
     */
    public function store_WhenAuthorized_CreatesRouteAndAssociatedWithPage(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', $attrs);

        $page = Page::all()->last();
        $this->assertCount(1, $page->routes);
    }

    /**
     * @test
     */
    public function store_WhenAuthorized_CreatedRouteIsCanonical(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', $attrs);

        $page = Page::all()->last();
        $this->assertEquals($page->routes[0]->getKey(), $page->canonical->getKey());
    }

    /**
     * @test
     */
    public function store_WhenAuthorized_CreatesBlocksWithinRegion(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', $attrs);

        $page = Page::all()->last();
        $regions = $page->blocksByRegion();

        $this->assertArrayHasKey('test-region', $regions);
        $this->assertCount(1, $regions['test-region']);

        $block = $regions['test-region'][0];
        $this->assertEquals($attrs['regions']['test-region'][0]['definition_name'], $block->definition_name);
        $this->assertEquals($attrs['regions']['test-region'][0]['definition_version'], $block->definition_version);
    }

    /**
     * @test
     */
    public function store_WhenAuthorized_Returns201(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', PageController::class . '@store', $this->getAttrs());
        $response->assertStatus(201);
    }

}
