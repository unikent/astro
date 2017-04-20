<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use Mockery;
use App\Models\Page;
use App\Models\Block;
use App\Models\Route;
use App\Http\Controllers\Api\v1\PageController;

class PageControllerTest extends ApiControllerTestCase {


    protected function getAttrs(Page $page = null, Route $route = null, Block $block = null)
    {
        $page = $page ?: factory(Page::class)->make();
        $route = $route ?: factory(Route::class)->states('withParent')->make([ 'page_id' => $page->getKey() ]);

        $block = $block ?: factory(Block::class)->make();

        return attrs_for($page) + [
            'route' => attrs_for($route),

            'regions' => [
                'test-region' => [
                    0 => attrs_for($block),
                ],
            ],
        ];
    }


    /**
     * @test
     * @group authentication
     */
    public function store_WhenUnauthenticated_Returns401(){
        $response = $this->action('POST', PageController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthenticated_ChecksAuthorization(){
        Gate::shouldReceive('authorize')->with('create', Page::class)->once();

        $this->authenticated();
        $response = $this->action('POST', PageController::class . '@store', [], $this->getAttrs());
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('POST', PageController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatesPage(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = Page::all()->last();
        $this->assertEquals($attrs['title'], $page->title);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatesRouteAndAssociatedWithPage(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = Page::all()->last();
        $this->assertCount(1, $page->routes);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatedRouteIsCanonical(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = Page::all()->last();
        $this->assertEquals($page->routes[0]->getKey(), $page->canonical->getKey());
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatesBlocksWithinRegion(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = Page::all()->last();
        $regions = $page->blocks->groupBy('region_name');

        $this->assertArrayHasKey('test-region', $regions);
        $this->assertCount(1, $regions['test-region']);

        $block = $regions['test-region'][0];
        $this->assertEquals($attrs['regions']['test-region'][0]['definition_name'], $block->definition_name);
        $this->assertEquals($attrs['regions']['test-region'][0]['definition_version'], $block->definition_version);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_Returns201(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', PageController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(201);
    }




    /**
     * @test
     * @group authentication
     */
    public function update_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $this->getAttrs());
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function update_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        Gate::shouldReceive('authorize')->with('update', Mockery::on(function($model) use ($page){
            return (is_a($model, Page::class) && ($model->getKey() == $page->getKey()));
        }))->once();

        $this->authenticated();
        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $this->getAttrs());
    }

    /**
     * @test
     * @group authorization
     */
    public function update_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $this->getAttrs());
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndPageNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('PUT', PageController::class . '@update', [ 123 ], $attrs);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_UpdatesPage(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $attrs = $this->getAttrs($page, $route); // Use the existing Page and Route, but update title
        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $this->assertEquals($attrs['title'], $page->title);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRouteHasNotChanged_DoesNotCreateNewRoute(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $attrs = $this->getAttrs($page, $route);
        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $this->assertCount(1, $page->routes);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRouteHasChanged_CreatesNewRouteAndAssociatesWithPage(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'route.parent_id', $route->parent_id);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $this->assertCount(2, $page->routes);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRouteHasChanged_NewRouteIsCanonical(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'route.parent_id', $route->parent_id);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $this->assertEquals($page->routes[1]->getKey(), $page->canonical->getKey());
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRegionIsEmpty_ClearsExistingBlocks(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $block = factory(Block::class)->create([ 'page_id' => $page->getKey() ]); // Default region is 'test-region'

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'regions.test-region', []);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $this->assertArrayNotHasKey('test-region', $page->blocks->groupBy('region_name'));
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRegionIsNotEmpty_UpdatesExistingBlocks(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $block = factory(Block::class)->create([ 'page_id' => $page->getKey() ]); // Default region is 'test-region'

        $attrs = $this->getAttrs($page, null, $block);
        array_set($attrs, 'regions.test-region.0.fields', [ 'fizz' => 'buzz' ]);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $regions = $page->blocks->groupBy('region_name');

        $this->assertCount(1, $regions);
        $this->assertEquals($regions['test-region'][0]->fields['fizz'], 'buzz');
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_Returns200(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $this->getAttrs());
        $response->assertStatus(200);
    }




    /**
     * @test
     * @group authentication
     */
    public function delete_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('DELETE', PageController::class . '@destroy', [ $page->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        Gate::shouldReceive('authorize')->with('delete', Mockery::on(function($model) use ($page){
            return (is_a($model, Page::class) && ($model->getKey() == $page->getKey()));
        }))->once();

        $this->authenticated();
        $response = $this->action('DELETE', PageController::class . '@destroy', [ $page->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('DELETE', PageController::class . '@destroy', [ $page->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function delete_WhenAuthorizedAndPageNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('DELETE', PageController::class . '@destroy', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function delete_WhenAuthorizedAndValid_DeletesThePage(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('DELETE', PageController::class . '@destroy', [ $page->getKey() ]);
        $this->assertNull(Page::find($page->id));
    }

    /**
     * @test
     */
    public function delete_WhenAuthorizedAndValid_DeletesAssociatedRoutes(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $routes = factory(Route::class, 2)->create([ 'parent_id' => $route->parent_id, 'page_id' => $route->page->getKey() ]);

        $page = $route->page;

        $count = Route::count();
        $response = $this->action('DELETE', PageController::class . '@destroy', [ $page->getKey() ]);

        $this->assertEquals($count-3, Route::count());
    }

    /**
     * @test
     */
    public function delete_WhenAuthorizedAndValid_Returns200(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('DELETE', PageController::class . '@destroy', [ $page->getKey() ]);
        $response->assertStatus(200);
    }

}
