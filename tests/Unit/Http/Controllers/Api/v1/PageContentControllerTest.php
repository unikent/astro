<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use Mockery;
use App\Models\PageContent;
use App\Models\Site;
use App\Models\Block;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\Revision;
use App\Http\Controllers\Api\v1\PageController;
use App\Http\Transformers\Api\v1\PageContentTransformer;
use Illuminate\Auth\Access\AuthorizationException;

class PageContentControllerTest extends ApiControllerTestCase {


    protected function getAttrs(PageContent $page = null, Page $route = null, Block $block = null)
    {
        $page = $page ?: factory(PageContent::class)->make();
        $route = $route ?: factory(Page::class)->states('withParent')->make([ 'page_id' => $page->getKey() ]);

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
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withDraft', 'isRoot' ])->create();

        // $page->publish(new PageContentTransformer);

        $response = $this->action('GET', PageController::class . '@show', [ $route->draft_id ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function show_WhenAuthenticated_ChecksAuthorization(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withDraft', 'isRoot' ])->create();

        // $page->publish(new PageContentTransformer);

        Gate::shouldReceive('authorize')->with('read', Mockery::type(PageContent::class))->once();

        $this->authenticated();
        $response = $this->action('GET', PageController::class . '@show', [ $route->page->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function show_WhenAuthenticatedAndUnauthorized_Returns403(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withDraft', 'isRoot' ])->create();

        // $page->publish(new PageContentTransformer);

        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', PageController::class . '@show', [ $route->page->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndPageNotFound_Returns404(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFound_Returns200(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withDraft', 'isRoot' ])->create();

        // $page->publish(new PageContentTransformer);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [ $route->page->getKey() ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFound_ReturnsJson(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withDraft', 'isRoot' ])->create();

        // $page->publish(new PageTrasformer);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [ $route->page->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertEquals($route->page->title, $json['data']['title']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundAndPublished_ReturnsActiveRouteInJson(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withDraft', 'isRoot' ])->create();
        $route->page->publish(new PageContentTransformer);

        // $page->publish(new PageContentTransformer);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [ $route->page->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('active_route', $json['data']);
        $this->assertEquals($route->slug, $json['data']['active_route']['slug']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesLayoutDefinition_IncludesLayoutDefinitionInJson(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withDraft', 'withParent' ])->create();
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
        return $this->markTestIncomplete();
        $r1 = factory(Page::class)->states([ 'withDraft', 'isRoot' ])->create();
        $r1->page->publish(new PageContentTransformer);

        $r2 = factory(Page::class)->create(array_except(attrs_for($r1), [ 'id', 'is_active' ]));

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [
            'page' => $r1->page->getKey(),
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
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
        $block = factory(Block::class)->create([ 'page_id' => $route->draft_id ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [
            'page' => $route->page->getKey(),
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
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
        $block = factory(Block::class)->create([ 'page_id' => $route->draft_id ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [
            'page' => $route->page->getKey(),
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
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
        $route->page->publish(new PageContentTransformer);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', PageController::class . '@show', [
            'page' => $route->page->getKey(),
            'include' => 'published'
        ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('published', $json['data']);
        $this->assertNotEmpty('published', $json['data']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesHistory_IncludesPublishedInHistory(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
        $page = $route->page;

        $block = factory(Block::class)->create([ 'page_id' => $page->getKey() ]);

        $page->publish(new PageContentTransformer);
        $page->publish(new PageContentTransformer);

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
        return $this->markTestIncomplete();
        $response = $this->action('POST', PageController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthenticated_ChecksAuthorization(){
        return $this->markTestIncomplete();
        Gate::shouldReceive('authorize')->with('create', Mockery::type(PageContent::class))->once();

        $this->authenticated();
        $response = $this->action('POST', PageController::class . '@store', [], $this->getAttrs());
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthenticatedAndUnauthorized_Returns403(){
        return $this->markTestIncomplete();
        $this->authenticatedAndUnauthorized();

        $response = $this->action('POST', PageController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatesPage(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = PageContent::all()->last();
        $this->assertEquals($attrs['title'], $page->title);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatesRouteAndAssociatedWithPage(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = PageContent::all()->last();
        $this->assertCount(1, $page->routes);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatedRouteIsInactive(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = PageContent::all()->last();
        $this->assertFalse($page->routes[0]->isActive());
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_CreatesBlocksWithinRegion(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = PageContent::all()->last();
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
        return $this->markTestIncomplete();
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
        return $this->markTestIncomplete();
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
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $site = factory(Site::class)->states('withPublishingGroup')->make();

        $attrs = $this->getAttrs();
        array_set($attrs, 'site', [
            'name' => $site->name,
            'publishing_group_id' => $site->publishing_group_id,
        ]);

        $response = $this->action('POST', PageController::class . '@store', [], $attrs);

        $page = PageContent::all()->last();
        $this->assertEquals($attrs['site']['name'], $page->routes[0]->site->name);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_Returns201(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', PageController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(201);
    }




    /**
     * @test
     * @group authentication
     */
    public function update_WhenUnauthenticated_Returns401(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $this->getAttrs());
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function update_WhenAuthenticated_ChecksAuthorization(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        Gate::shouldReceive('authorize')->with('update', Mockery::on(function($model) use ($page){
            return (is_a($model, PageContent::class) && ($model->getKey() == $page->getKey()));
        }))->once();

        $this->authenticated();
        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $this->getAttrs());
    }

    /**
     * @test
     * @group authorization
     */
    public function update_WhenAuthenticatedAndUnauthorized_Returns403(){
        return $this->markTestIncomplete();
        $this->authenticatedAndUnauthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $this->getAttrs());
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndPageNotFound_Returns404(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $attrs = $this->getAttrs();
        $response = $this->action('PUT', PageController::class . '@update', [ 123 ], $attrs);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_UpdatesPage(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();
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
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $attrs = $this->getAttrs($page, $route);
        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $this->assertCount(1, $page->routes);
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRouteHasChanged_CreatesNewInactiveRouteToPage(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();

        // Publish the Routes/Pages
        $route->parent->page->publish(new PageContentTransformer);
        $route->page->publish(new PageContentTransformer);

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
    public function update_WhenAuthorizedAndValid_WhenRouteHasChanged_RemovesOtherInactiveRoutesToPage(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withPublishedParent')->create();
        $route->page->publish(new PageContentTransformer);

        $inactive = factory(Page::class, 2)->create([ 'page_id' => $route->page_id, 'parent_id' => $route->parent_id ]);

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
    public function update_WhenAuthorizedAndValid_WhenRouteIsMoved_CreatesInactiveRouteAtNewLocation(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $l1 = factory(Page::class)->states('withPage', 'withPublishedParent')->create();
        $l1->page->publish(new PageContentTransformer);

        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l2->page->publish(new PageContentTransformer);

        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);
        $l3->page->publish(new PageContentTransformer);

        $page = $l2->page;

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'route.parent_id', $l1->parent_id);

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();

        $activeRoute = $page->activeRoute;
        $this->assertEquals($l1->getKey(), $activeRoute->parent_id);    // $activeRoute is unchanged

        $draftRoute = $page->draftRoute;
        $this->assertEquals($l1->parent_id, $draftRoute->parent_id);   // $draftRoute has been created
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRouteHasChildrenAndIsMoved_CreatesInactiveRoutesForChildren(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $root = factory(Page::class)->states('withPage', 'isRoot')->create();
        $root->page->publish(new PageContentTransformer);

        $l1 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $root->getKey() ]);
        $l1->page->publish(new PageContentTransformer);

        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l2->page->publish(new PageContentTransformer);

        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);
        $l3->page->publish(new PageContentTransformer);

        $page = $l2->page;
        $childPage = $l3->page;

        $attrs = $this->getAttrs($page);
        array_set($attrs, 'route.parent_id', $root->getKey());

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $attrs);

        $page = $page->fresh();
        $childPage = $childPage->fresh();

        $activeRoute = $childPage->activeRoute;
        $this->assertEquals($l2->getKey(), $activeRoute->parent_id);    // child $activeRoute is unchanged

        $draftRoute = $childPage->draftRoute;
        $this->assertEquals($page->draftRoute->getKey(), $draftRoute->parent_id);   // child $draftRoute has been created
    }

    /**
     * @test
     */
    public function update_WhenAuthorizedAndValid_WhenRegionIsEmpty_ClearsExistingBlocks(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();
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
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();
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
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent', 'withSite')->create();
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
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent', 'withSite')->create();
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
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent', 'withSite')->create();
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
        return $this->markTestIncomplete();
        $this->authenticated();

        $route = factory(Page::class)->states('withPage', 'withParent', 'withSite')->create();
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
        return $this->markTestIncomplete();
        $this->authenticated();

        $route = factory(Page::class)->states('withPage', 'withParent', 'withSite')->create();
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
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent', 'withSite')->create();
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
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('PUT', PageController::class . '@update', [ $page->getKey() ], $this->getAttrs());
        $response->assertStatus(200);
    }



    /**
     * @test
     * @group authentication
     */
    public function publish_WhenUnauthenticated_Returns401(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'isRoot')->create();
        $page = $route->page;

        $response = $this->action('POST', PageController::class . '@publish', [ $page->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function publish_WhenAuthenticated_ChecksAuthorization(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'isRoot')->create();
        $page = $route->page;

        Gate::shouldReceive('authorize')->with('publish', Mockery::on(function($model) use ($page){
            return (is_a($model, PageContent::class) && ($model->getKey() == $page->getKey()));
        }))->once();

        $this->authenticated();
        $response = $this->action('POST', PageController::class . '@publish', [ $page->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function publish_WhenAuthenticatedAndUnauthorized_Returns403(){
        return $this->markTestIncomplete();
        $this->authenticatedAndUnauthorized();

        $route = factory(Page::class)->states('withPage', 'isRoot')->create();
        $page = $route->page;

        $response = $this->action('POST', PageController::class . '@publish', [ $page->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function publish_WhenAuthorizedAndPageNotFound_Returns404(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', PageController::class . '@publish', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function publish_WhenAuthorizedHasUnpublishedParents_Returns406(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('POST', PageController::class . '@publish', [ $page->getKey() ]);

        $response->assertStatus(406);

        $json = $response->json();
        $this->assertArrayHasKey('errors', $json);
        $this->assertNotEmpty($json['errors']);
    }

    /**
     * @test
     */
    public function publish_WhenAuthorizedAndValid_CreatesNewPublishedPage(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'isRoot')->create();
        $page = $route->page;

        $count = Revision::count();

        $response = $this->action('POST', PageController::class . '@publish', [ $page->getKey() ]);
        $this->assertEquals($count + 1, Revision::count());
    }

    /**
     * @test
     * @group integration
     *
     * This test covers deep functionality within $page->publish(); its important from
     * a behavioural perspective so have gone for a belt-and-braces integration test.
     */
    public function publish_WhenAuthorizedAndValidAndHasDraftRoute_DraftRouteBecomesActive(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $active = factory(Page::class)->states('withPage', 'isRoot')->create();

        $page = $active->page;
        $page->publish(new PageContentTransformer);

        $draft = factory(Page::class)->create(array_except(attrs_for($active), [ 'id', 'is_active' ]));

        $this->action('POST', PageController::class . '@publish', [ 'page' => $page->getKey() ]);

        $page = $page->fresh();
        $this->assertCount(1, $page->routes);
        $this->assertEquals($draft->getKey(), $page->activeRoute->getKey());
    }

    /**
     * @test
     * @group integration
     *
     * This test covers deep functionality within $page->publish(); its important from
     * a behavioural perspective so have gone for a belt-and-braces integration test.
     */
    public function publish_WhenAuthorizedAndValidAndHasDraftRoute_CreatesNewRedirect(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $active = factory(Page::class)->states('withPage', 'isRoot')->create();

        $page = $active->page;
        $page->publish(new PageContentTransformer);

        $draft = factory(Page::class)->create(array_except(attrs_for($active), [ 'id', 'is_active' ]));

        $count = Redirect::count();

        $this->action('POST', PageController::class . '@publish', [ 'page' => $page->getKey() ]);
        $this->assertEquals($count+1, Redirect::count());
    }

    /**
     * @test
     */
    public function publish_WhenAuthorizedAndValid_Returns200(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'isRoot')->create();
        $page = $route->page;

        $response = $this->action('POST', PageController::class . '@publish', [ 'page' => $page->getKey() ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function publish_WhenAuthorizedAndValid_ReturnsBakedJson(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'isRoot')->create();
        $page = $route->page;

        $response = $this->action('POST', PageController::class . '@publish', [ 'page' => $page->getKey() ]);
        $json = $response->json();

        $this->assertEquals(json_decode($page->published->bake, TRUE), $json);
    }



    /**
     * @test
     * @group authentication
     */
    public function publishTree_WhenUnauthenticated_Returns401(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withPublishedParent')->create();

        $l1 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $route->getKey() ]);
        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);

        $response = $this->action('POST', PageController::class . '@publishTree', [ $l1->page->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function publishTree_WhenAuthenticated_ChecksAuthorization(){
        return $this->markTestIncomplete();
        $l1 = factory(Page::class)->states('withPage', 'withPublishedParent')->create();
        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);

        Gate::shouldReceive('authorize')->with('publish', Mockery::type(PageContent::class))->times(3)->andReturn(true);

        $this->authenticated();
        $response = $this->action('POST', PageController::class . '@publishTree', [ $l1->page->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function publishTree_WhenAuthenticatedAndUnauthorized_Returns403(){
        return $this->markTestIncomplete();
        $l1 = factory(Page::class)->states('withPage', 'withPublishedParent')->create();
        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);

        $this->authenticatedAndUnauthorized();
        $response = $this->action('POST', PageController::class . '@publishTree', [ $l1->page->getKey() ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function publishTree_WhenAuthorizedAndPageNotFound_Returns404(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', PageController::class . '@publishTree', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function publishTree_WhenAuthorizedHasUnpublishedParents_Returns406(){
        return $this->markTestIncomplete();
        $l1 = factory(Page::class)->states('withPage', 'withParent')->create();
        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);

        $this->authenticatedAndAuthorized();
        $response = $this->action('POST', PageController::class . '@publishTree', [ $l1->page->getKey() ]);

        $response->assertStatus(406);

        $json = $response->json();
        $this->assertArrayHasKey('errors', $json);
        $this->assertNotEmpty($json['errors']);
    }

    /**
     * @test
     */
    public function publishTree_WhenAuthenticatedAndAuthorizes_AllRoutesInTreeAreMadeActive(){
        return $this->markTestIncomplete();
        $l1 = factory(Page::class)->states('withPage', 'withPublishedParent')->create();
        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);

        $this->authenticatedAndAuthorized();
        $response = $this->action('POST', PageController::class . '@publishTree', [ $l1->page->getKey() ]);

        $l1 = $l1->fresh();
        $this->assertTrue($l1->isActive());

        $l2 = $l2->fresh();
        $this->assertTrue($l2->isActive());

        $l3 = $l3->fresh();
        $this->assertTrue($l3->isActive());
    }

    /**
     * @test
     */
    public function publishTree_WhenAuthenticatedAndAuthorizes_AllPagesArePublished(){
        return $this->markTestIncomplete();
        $l1 = factory(Page::class)->states('withPage', 'withPublishedParent')->create();
        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);

        $count = Revision::count();

        $this->authenticatedAndAuthorized();
        $response = $this->action('POST', PageController::class . '@publishTree', [ $l1->page->getKey() ]);

        $this->assertEquals($count+3, Revision::count());
    }

    /**
     * @test
     */
    public function publishTree_WhenAuthorizedAndValid_Returns200(){
        return $this->markTestIncomplete();
        $l1 = factory(Page::class)->states('withPage', 'withPublishedParent')->create();
        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);

        $this->authenticatedAndAuthorized();
        $response = $this->action('POST', PageController::class . '@publishTree', [ $l1->page->getKey() ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function publishTree_WhenAuthorizedAndValid_ReturnsBakedJson(){
        return $this->markTestIncomplete();
        $l1 = factory(Page::class)->states('withPage', 'withPublishedParent')->create();
        $l2 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l1->getKey() ]);
        $l3 = factory(Page::class)->states('withPage')->create([ 'parent_id' => $l2->getKey() ]);

        $this->authenticatedAndAuthorized();
        $response = $this->action('POST', PageController::class . '@publishTree', [ $l1->page->getKey() ]);

        $json = $response->json();
        $this->assertEquals(json_decode($l1->page->published->bake, TRUE), $json);
    }



    /**
     * @test
     * @group authentication
     */
    public function revert_WhenUnauthenticated_Returns401(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'isRoot')->create();

        $page = $route->page;
        $page->publish(new PageContentTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [
            'page' => $page->getKey(),
            'published_page_id' => $page->published->getKey()
        ]);

        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function revert_WhenAuthenticated_ChecksAuthorization(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'isRoot')->create();

        $page = $route->page;
        $page->publish(new PageContentTransformer);

        Gate::shouldReceive('authorize')->with('revert', Mockery::on(function($model) use ($page){
            return (is_a($model, PageContent::class) && ($model->getKey() == $page->getKey()));
        }))->once();

        $this->authenticated();
        $response = $this->action('POST', PageController::class . '@revert', [
            'page' => $page->getKey(),
            'published_page_id' => $page->published->getKey()
        ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function revert_WhenAuthenticatedAndUnauthorized_Returns403(){
        return $this->markTestIncomplete();
        $this->authenticatedAndUnauthorized();

        $route = factory(Page::class)->states('withPage', 'isRoot')->create();

        $page = $route->page;
        $page->publish(new PageContentTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [ 'page' => $page->getKey(), 'published_page_id' => $page->published->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndPageNotFound_Returns404(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', PageController::class . '@revert', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndPublishedPageNotFound_Returns404(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'isRoot')->create();

        $page = $route->page;
        $page->publish(new PageContentTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [ $page->getKey(), 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndValid_RevertsThePage(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'isRoot')->create();

        $page = $route->page;
        $page->publish(new PageContentTransformer);

        $title = $page->title;
        $page->title = 'Foobar!';
        $page->save();

        $this->assertEquals('Foobar!', $page->title);

        $response = $this->action('POST', PageController::class . '@revert', [
            'page' => $page->getKey(),
            'published_page_id' => $page->published->getKey()
        ]);

        $page = $page->fresh();
        $this->assertEquals($title, $page->title);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndValid_Returns200(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'isRoot')->create();

        $page = $route->page;
        $page->publish(new PageContentTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [
            'page' => $page->getKey(),
            'published_page_id' => $page->published->getKey()
        ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function revert_WhenAuthorizedAndValid_ReturnsJson(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $route = factory(Page::class)->states('withPage', 'isRoot')->create();

        $page = $route->page;
        $page->publish(new PageContentTransformer);

        $response = $this->action('POST', PageController::class . '@revert', [ 'page' => $page->getKey(), 'published_page_id' => $page->published->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertNotEmpty($json['data']);

        $this->assertArrayHasKey('active_route', $json['data']);
        $this->assertNotEmpty($json['data']['active_route']);
    }



    /**
     * @test
     * @group authentication
     */
    public function destroy_WhenUnauthenticated_Returns401(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('DELETE', PageController::class . '@destroy', [ $page->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function destroy_WhenAuthenticated_ChecksAuthorization(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        Gate::shouldReceive('authorize')->with('delete', Mockery::on(function($model) use ($page){
            return (is_a($model, PageContent::class) && ($model->getKey() == $page->getKey()));
        }))->once();

        $this->authenticated();
        $response = $this->action('DELETE', PageController::class . '@destroy', [ $page->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function destroy_WhenAuthenticatedAndUnauthorized_Returns403(){
        return $this->markTestIncomplete();
        $this->authenticatedAndUnauthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('DELETE', PageController::class . '@destroy', [ $page->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorizedAndPageNotFound_Returns404(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $response = $this->action('DELETE', PageController::class . '@destroy', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorizedAndValid_SoftDeletesThePage(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();

        $this->authenticatedAndAuthorized();
        $response = $this->action('DELETE', PageController::class . '@destroy', [ $route->page->getKey() ]);

        $this->assertNull(PageContent::find($route->page->id));
        $this->assertEquals(1, PageContent::withTrashed()->where('id', '=', $route->page->getKey())->count());
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorizedAndValid_DoesNotDeleteAssociatedRoutes(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();

        $this->authenticatedAndAuthorized();
        $response = $this->action('DELETE', PageController::class . '@destroy', [ $route->page->getKey() ]);

        $this->assertInstanceOf(Page::class, Page::find($route->getKey()));
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorizedAndValid_DoesNotDeleteAssociatedRedirects(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $redirect = Redirect::createFromRoute($route);

        $this->authenticatedAndAuthorized();
        $response = $this->action('DELETE', PageController::class . '@destroy', [ $route->page->getKey() ]);

        $this->assertInstanceOf(Redirect::class, Redirect::find($redirect->getKey()));
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorizedAndValid_Returns200(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();

        $this->authenticatedAndAuthorized();
        $response = $this->action('DELETE', PageController::class . '@destroy', [ $route->page->getKey() ]);

        $response->assertStatus(200);
    }



    /**
     * @test
     * @group authentication
     */
    public function forceDestroy_WhenUnauthenticated_Returns401(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('DELETE', PageController::class . '@forceDestroy', [ $page->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function forceDestroy_WhenAuthenticated_ChecksAuthorization(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        Gate::shouldReceive('authorize')->with('forceDelete', Mockery::on(function($model) use ($page){
            return (is_a($model, PageContent::class) && ($model->getKey() == $page->getKey()));
        }))->once();

        $this->authenticated();
        $response = $this->action('DELETE', PageController::class . '@forceDestroy', [ $page->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function forceDestroy_WhenAuthenticatedAndUnauthorized_Returns403(){
        return $this->markTestIncomplete();
        $this->authenticatedAndUnauthorized();

        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $page = $route->page;

        $response = $this->action('DELETE', PageController::class . '@forceDestroy', [ $page->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function forceDestroy_WhenAuthorizedAndPageNotFound_Returns404(){
        return $this->markTestIncomplete();
        $this->authenticatedAndAuthorized();

        $response = $this->action('DELETE', PageController::class . '@forceDestroy', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function forceDestroy_WhenAuthorizedAndValid_HardDeletesThePage(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();

        $this->authenticatedAndAuthorized();
        $response = $this->action('DELETE', PageController::class . '@forceDestroy', [ $route->page->getKey() ]);

        $this->assertEquals(0, PageContent::withTrashed()->where('id', '=', $route->page->getKey())->count());
    }

    /**
     * @test
     */
    public function forceDestroy_WhenAuthorizedAndValid_HardDeletesAssociatedRoutes(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();

        $this->authenticatedAndAuthorized();
        $response = $this->action('DELETE', PageController::class . '@forceDestroy', [ $route->page->getKey() ]);

        $this->assertNull(Page::find($route->getKey()));
    }

    /**
     * @test
     */
    public function forceDestroy_WhenAuthorizedAndValid_HardDeletesAssociatedRedirects(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();
        $redirect = Redirect::createFromRoute($route);

        $this->authenticatedAndAuthorized();
        $response = $this->action('DELETE', PageController::class . '@forceDestroy', [ $route->page->getKey() ]);

        $this->assertNull(Redirect::find($redirect->getKey()));
    }

    /**
     * @test
     */
    public function forceDestroy_WhenAuthorizedAndValid_DoesNotDeletePublishedPages(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withPublishedParent')->create();
        $route->page->publish(new PageContentTransformer);

        $published = $route->page->published;

        $this->authenticatedAndAuthorized();
        $response = $this->action('DELETE', PageController::class . '@forceDestroy', [ $route->page->getKey() ]);

        $this->assertInstanceOf(Revision::class, Revision::find($published->getKey()));
    }

    /**
     * @test
     */
    public function forceDestroy_WhenAuthorizedAndValid_Returns200(){
        return $this->markTestIncomplete();
        $route = factory(Page::class)->states('withPage', 'withParent')->create();

        $this->authenticatedAndAuthorized();
        $response = $this->action('DELETE', PageController::class . '@forceDestroy', [ $route->page->getKey() ]);

        $response->assertStatus(200);
    }

}
