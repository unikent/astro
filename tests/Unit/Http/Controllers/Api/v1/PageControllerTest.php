<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use Mockery;
use App\Models\Page;
use App\Models\Site;
use App\Models\Block;
use App\Models\Route;
use App\Models\PublishedPage;
use App\Http\Controllers\Api\v1\PageController;
use App\Http\Transformers\Api\v1\PageTransformer;
use Illuminate\Auth\Access\AuthorizationException;

class PageControllerTest extends ApiControllerTestCase {


    protected function getAttrs(Page $page = null, Route $route = null, Block $block = null)
    {
        $page = $page ?: factory(Page::class)->make();
        $route = $route ?: factory(Route::class)->states('withParent')->make([ 'page_id' => $page->getKey() ]);

        $block = $block ?: factory(Block::class)->states('useTestBlock')->make();

        return attrs_for($page) + [
            'route' => attrs_for($route),

            'blocks' => [
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
    public function show_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $page = $route->page;

        $response = $this->action('GET', PageController::class . '@show', [ $page->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function show_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $page = $route->page;

        Gate::shouldReceive('authorize')->with('read', Mockery::type(Page::class))->once();

        $this->authenticated();
        $response = $this->action('GET', PageController::class . '@show', [ $page->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function show_WhenAuthenticatedAndUnauthorized_Returns403(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $page = $route->page;

        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', PageController::class . '@show', [ $page->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndPageNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFound_Returns200(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $page = $route->page;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [ $page->getKey() ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFound_ReturnsJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $page = $route->page;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [ $page->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertEquals($page->title, $json['data']['title']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFound_ReturnsCanonicalRouteInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $route->makeCanonical();

        $page = $route->page;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [ $page->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('canonical', $json['data']);
        $this->assertEquals($route->slug, $json['data']['canonical']['slug']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesLayoutDefinition_IncludesLayoutDefinitionInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $route->makeCanonical();

        $page = $route->page;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [
            'page' => $page->getKey(),
            'include' => 'layout_definition',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('layout_definition', $json['data']);
        $this->assertEquals($page->layout_name, $json['data']['layout_definition']['name']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesRoutes_IncludesRoutesInJson(){
        $r1 = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $r2 = factory(Route::class)->create([ 'page_id' => $r1->page_id, 'parent_id' => $r1->parent_id ]);

        $r1->makeCanonical();

        $page = $r1->page;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [
            'page' => $page->getKey(),
            'include' => 'routes',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('routes', $json['data']);
        $this->assertCount(2, $json['data']['routes']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesBlocks_IncludesBlocksByRegionInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $route->makeCanonical();

        $page = $route->page;
        $block = factory(Block::class)->create([ 'page_id' => $page->getKey() ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [
            'page' => $page->getKey(),
            'include' => 'blocks',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('blocks', $json['data']);
        $this->assertArrayHasKey('test-region', $json['data']['blocks']);
        $this->assertCount(1, $json['data']['blocks']['test-region']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesBlocksDefinition_IncludesBlocksAndDefinitionsInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $route->makeCanonical();

        $page = $route->page;
        $block = factory(Block::class)->create([ 'page_id' => $page->getKey() ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [
            'page' => $page->getKey(),
            'include' => 'blocks.definition',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('blocks', $json['data']);
        $this->assertArrayHasKey('test-region', $json['data']['blocks']);

        $this->assertCount(1, $json['data']['blocks']['test-region']);
        $this->assertArrayHasKey('definition', $json['data']['blocks']['test-region'][0]);
        $this->assertEquals($block->definition_name, $json['data']['blocks']['test-region'][0]['definition']['name']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesPublished_IncludesPublishedInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $route->page->publish(new PageTransformer);

        $page = $route->page->fresh();

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', PageController::class . '@show', [ 'page' => $page->getKey(), 'include' => 'published' ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('published', $json['data']);
        $this->assertNotEmpty('published', $json['data']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesHistory_IncludesPublishedInHistory(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $route->page->publish(new PageTransformer);

        $block = factory(Block::class)->create([ 'page_id' => $route->page->getKey() ]);

        $page = $route->page->fresh();
        $route->page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', PageController::class . '@show', [ 'page' => $page->getKey(), 'include' => 'history' ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('history', $json['data']);
        $this->assertCount(2, $json['data']['history']);
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
        Gate::shouldReceive('authorize')->with('create', Mockery::type(Page::class))->once();

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
    public function store_WhenAuthorizedAndValid_CreatedRouteIsInactive(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = Page::all()->last();
        $this->assertFalse($page->routes[0]->isActive());
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatedRouteIsNotCanonical(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = Page::all()->last();
        $this->assertFalse($page->routes[0]->isCanonical());
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatesBlocksWithinRegion(){
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = Page::all()->last();
        $blocks = $page->blocks->groupBy('region_name');

        $this->assertArrayHasKey('test-region', $blocks);
        $this->assertCount(1, $blocks['test-region']);

        $block = $blocks['test-region'][0];
        $this->assertEquals($attrs['blocks']['test-region'][0]['definition_name'], $block->definition_name);
        $this->assertEquals($attrs['blocks']['test-region'][0]['definition_version'], $block->definition_version);
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthorizedAndValidPageIsASite_WhenSiteFieldArePresent_AuthorizesSiteOperation(){
        $this->authenticated();

        Gate::shouldReceive('authorize')->with('create', Mockery::type(Site::class))->once();

        Gate::shouldReceive('authorize');

        $site = factory(Site::class)->states('withPublishingGroup')->make();

        $attrs = $this->getAttrs();
        array_set($attrs, 'site', [
            'name' => $site->name,
            'publishing_group_id' => $site->publishing_group_id,
        ]);

        $this->action('POST', PageController::class . '@store', [], $attrs);
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthorizedAndValidPageIsASite_WhenSiteFieldArePresentButUnauthorizedOnSite_Returns403(){
        $this->authenticated();

        Gate::shouldReceive('authorize')->with('create', Mockery::type(Site::class))->andThrow(AuthorizationException::class);
        Gate::shouldReceive('authorize');

        $site = factory(Site::class)->states('withPublishingGroup')->make();

        $attrs = $this->getAttrs();
        array_set($attrs, 'site', [
            'name' => $site->name,
            'publishing_group_id' => $site->publishing_group_id,
        ]);

        $response = $this->action('POST', PageController::class . '@store', [], $attrs);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValidAndSiteFieldsArePresent_CreatesSiteAndAssociatesWithRoute(){
        $this->authenticatedAndAuthorized();

        $site = factory(Site::class)->states('withPublishingGroup')->make();

        $attrs = $this->getAttrs();
        array_set($attrs, 'site', [
            'name' => $site->name,
            'publishing_group_id' => $site->publishing_group_id,
        ]);

        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = Page::all()->last();
        $this->assertEquals($attrs['site']['name'], $page->routes[0]->site->name);
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
        $route->makeActive();

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
    public function update_WhenAuthorizedAndValid_WhenRouteHasChanged_CreatesNewInactiveRoute(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $route->page->publish(new PageTransformer); // Makes Route active and canonical in a realistic way.

        $page = $route->page;
        $count = $page->routes->count();

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'route.parent_id', $route->parent_id);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $this->assertEquals($count + 1, $page->routes->count());
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRouteHasChanged_RemovesOtherInactiveRoutes(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $route->page->publish(new PageTransformer); // Makes Route active and canonical in a realistic way.

        $inactive = factory(Route::class, 2)->create([ 'page_id' => $route->page_id, 'parent_id' => $route->parent_id ]);

        $page = $route->page->fresh();

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'route.parent_id', $route->parent_id);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $this->assertEquals(1, $page->routes()->active(false)->count());
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRouteHasChanged_DoesNotAlterCanonicalStatus(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $route->page->publish(new PageTransformer); // Makes Route active and canonical in a realistic way.

        $inactive = factory(Route::class, 2)->create([ 'page_id' => $route->page_id, 'parent_id' => $route->parent_id ]);

        $page = $route->page->fresh();

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'route.parent_id', $route->parent_id);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $this->assertEquals($route->getKey(), $page->canonical->getKey());
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
        array_set($attrs, 'blocks.test-region', []);

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

        $block = factory(Block::class)->states('useTestBlock')
            ->create([ 'page_id' => $page->getKey() ]); // Default region is 'test-region'

        $attrs = $this->getAttrs($page, null, $block);
        array_set($attrs, 'blocks.test-region.0.fields.widget_title', 'Fizzbuzz');

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $blocks = $page->blocks->groupBy('region_name');

        $this->assertCount(1, $blocks);
        $this->assertEquals($blocks['test-region'][0]->fields['widget_title'], 'Fizzbuzz');
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValidAndIsASite_WhenSiteIdIsAbsent_DoesNotBreakSiteAssociation(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent', 'withSite')->create();
        $route->makeActive();

        $page = $route->page;
        $site = $route->site;

        $attrs = $this->getAttrs($page);
        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $route = $route->fresh();
        $this->assertEquals($site->getKey(), $route->site->getKey());
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValidPageIsASite_WhenSiteIdIsPresent_DoesNotBreakSiteAssociation(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent', 'withSite')->create();
        $route->makeActive();

        $page = $route->page;
        $site = $route->site;

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'site_id', $site->getKey());

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $route = $route->fresh();
        $this->assertEquals($site->getKey(), $route->site->getKey());
        $this->assertEquals($site->name, $route->site->name);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValidPageIsASite_WhenSiteIdIsPresent_DoesNotEditSite(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent', 'withSite')->create();
        $route->makeActive();

        $page = $route->page;
        $site = $route->site;

        $attrs = $this->getAttrs($page);
        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $route = $route->fresh();
        $this->assertEquals($site->name, $route->site->name);
    }

    /**
     * @test
     * @group authorization
     */
    public function update_WhenAuthorizedAndValidPageIsASite_WhenSiteFieldArePresent_AuthorizesSiteOperation(){
        $this->authenticated();

        $route = factory(Route::class)->states('withPage', 'withParent', 'withSite')->create();
        $route->makeActive();

        $page = $route->page;
        $site = $route->site;

        Gate::shouldReceive('authorize')->with('update', Mockery::on(function($model) use ($site){
            return (is_a($model, Site::class) && ($model->getKey() == $site->getKey()));
        }))->once();

        Gate::shouldReceive('authorize');

        $attrs = $this->getAttrs();
        array_set($attrs, 'site_id', $site->getKey());
        array_set($attrs, 'site', [
            'name' => $site->name,
            'publishing_group_id' => $site->publishing_group_id,
        ]);

        $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);
    }

    /**
     * @test
     * @group authorization
     */
    public function update_WhenAuthorizedAndValidPageIsASite_WhenSiteFieldArePresentButUnauthorizedOnSite_Returns403(){
        $this->authenticated();

        $route = factory(Route::class)->states('withPage', 'withParent', 'withSite')->create();
        $route->makeActive();

        $page = $route->page;
        $site = $route->site;

        Gate::shouldReceive('authorize')->with('update', Mockery::type(Site::class))->andThrow(AuthorizationException::class);
        Gate::shouldReceive('authorize');

        $attrs = $this->getAttrs();
        array_set($attrs, 'site_id', $site->getKey());
        array_set($attrs, 'site', [
            'name' => $site->name,
            'publishing_group_id' => $site->publishing_group_id,
        ]);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValidPageIsASite_WhenSiteIdIsPresentAndSiteFieldsArePresent_UpdatesSite(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent', 'withSite')->create();
        $route->makeActive();

        $page = $route->page;
        $site = $route->site;

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'site_id', $site->getKey());
        array_set($attrs, 'site', [
            'name' => 'Foobar!',
            'publishing_group_id' => $site->publishing_group_id,
        ]);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $route = $route->fresh();
        $this->assertEquals('Foobar!', $route->site->name);
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
    public function publish_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('POST', PageController::class . '@publish', [ $page->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function publish_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        Gate::shouldReceive('authorize')->with('publish', Mockery::on(function($model) use ($page){
            return (is_a($model, Page::class) && ($model->getKey() == $page->getKey()));
        }))->once();

        $this->authenticated();
        $response = $this->action('POST', PageController::class . '@publish', [ $page->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function publish_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('POST', PageController::class . '@publish', [ $page->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function publish_WhenAuthorizedAndPageNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', PageController::class . '@publish', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function publish_WhenAuthorizedAndValid_CreatesNewPublishedPage(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $count = PublishedPage::count();

        $response = $this->action('POST', PageController::class . '@publish', [ $page->getKey() ]);
        $this->assertEquals($count + 1, PublishedPage::count());
    }

    /**
     * @test
     */
    public function publish_WhenAuthorizedAndValid_Returns200(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('POST', PageController::class . '@publish', [ 'page' => $page->getKey() ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function publish_WhenAuthorizedAndValid_ReturnsBakedJson(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('POST', PageController::class . '@publish', [ 'page' => $page->getKey() ]);
        $json = $response->json();

        $this->assertEquals(json_decode($page->published->bake, TRUE), $json);
    }



    /**
     * @test
     * @group authentication
     */
    public function revert_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states('withPage', 'withParent')->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [ 'page' => $page->getKey(), 'published_page_id' => $page->published->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function revert_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states('withPage', 'withParent')->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

        Gate::shouldReceive('authorize')->with('revert', Mockery::on(function($model) use ($page){
            return (is_a($model, Page::class) && ($model->getKey() == $page->getKey()));
        }))->once();

        $this->authenticated();
        $response = $this->action('POST', PageController::class . '@revert', [ 'page' => $page->getKey(), 'published_page_id' => $page->published->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function revert_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [ 'page' => $page->getKey(), 'published_page_id' => $page->published->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndPageNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', PageController::class . '@revert', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndPublishedPageNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [ $page->getKey(), 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndValid_RevertsThePage(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

        $title = $page->title;
        $page->title = 'Foobar!';
        $page->save();

        $this->assertEquals('Foobar!', $page->title);

        $response = $this->action('POST', PageController::class . '@revert', [ 'page' => $page->getKey(), 'published_page_id' => $page->published->getKey() ]);

        $page = $page->fresh();
        $this->assertEquals($title, $page->title);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndValid_Returns200(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [ 'page' => $page->getKey(), 'published_page_id' => $page->published->getKey() ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndValid_ReturnsJson(){
        $this->authenticatedAndAuthorized();

        $route = factory(Route::class)->states('withPage', 'withParent')->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [ 'page' => $page->getKey(), 'published_page_id' => $page->published->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertNotEmpty($json['data']);

        $this->assertArrayHasKey('canonical', $json['data']);
        $this->assertNotEmpty($json['data']['canonical']);
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
