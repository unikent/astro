<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use Mockery;
use App\Models\Block;
use App\Models\Route;
use App\Http\Controllers\Api\v1\RouteController;
use Illuminate\Auth\Access\AuthorizationException;

class RouteControllerTest extends ApiControllerTestCase {

    /**
     * @test
     * @group authentication
     */
    public function resolve_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function resolve_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        Gate::shouldReceive('authorize')->with('read', Mockery::type(Route::class))->once();

        $this->authenticated();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function resolve_WhenAuthenticatedAndUnauthorized_Returns403(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndPathNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => '/foobar' ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFound_Returns200(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFound_ReturnsJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertEquals($route->id, $json['data']['id']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundRequestIncludesParent_IncludesParentRouteInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [
            'path' => $route->path,
            'include' => 'parent',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('parent', $json['data']);
        $this->assertEquals($route->parent->slug, $json['data']['parent']['slug']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundRequestIncludesSite_IncludesSiteInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withSite', 'withParent' ])->create();

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [
            'path' => $route->path,
            'include' => 'site',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('site', $json['data']);
        $this->assertEquals($route->site->name, $json['data']['site']['name']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundRequestIncludesPageLayoutDefinition_IncludesPageLayoutDefinitionInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $page = $route->page;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [
            'path' => $route->path,
            'include' => 'page.layout_definition',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('page', $json['data']);
        $this->assertArrayHasKey('layout_definition', $json['data']['page']);
        $this->assertEquals($page->layout_name, $json['data']['page']['layout_definition']['name']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundRequestIncludesPageCanonical_IncludesPageCanonicalRouteInJson(){
        $r1 = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $r2 = factory(Route::class)->create([ 'page_id' => $r1->page_id, 'parent_id' => $r1->parent_id ]);

        $r1->makeCanonical();

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [
            'path' => $r2->path,
            'include' => 'page.canonical',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('page', $json['data']);
        $this->assertArrayHasKey('canonical', $json['data']['page']);
        $this->assertEquals($r1->slug, $json['data']['page']['canonical']['slug']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundRequestIncludesPageRoutes_IncludesPageRoutesInJson(){
        $r1 = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $r2 = factory(Route::class)->create([ 'page_id' => $r1->page_id, 'parent_id' => $r1->parent_id ]);

        $page = $r1->page;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [
            'path' => $r1->path,
            'include' => 'page.routes',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('page', $json['data']);
        $this->assertArrayHasKey('routes', $json['data']['page']);
        $this->assertCount(2, $json['data']['page']['routes']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundRequestIncludesPageBlocks_IncludesPageBlocksByRegionInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $page = $route->page;
        $block = factory(Block::class)->create([ 'page_id' => $page->getKey() ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [
            'path' => $route->path,
            'include' => 'page.blocks',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('page', $json['data']);
        $this->assertArrayHasKey('blocks', $json['data']['page']);
        $this->assertArrayHasKey('test-region', $json['data']['page']['blocks']);
        $this->assertCount(1, $json['data']['page']['blocks']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundRequestIncludesPageBlocksDefinition_IncludesPageBlocksAndDefinitionsInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $page = $route->page;
        $block = factory(Block::class)->create([ 'page_id' => $page->getKey() ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [
            'path' => $route->path,
            'include' => 'page.blocks.definition',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('page', $json['data']);
        $this->assertArrayHasKey('blocks', $json['data']['page']);
        $this->assertArrayHasKey('test-region', $json['data']['page']['blocks']);
        $this->assertCount(1, $json['data']['page']['blocks']['test-region']);

        $this->assertArrayHasKey('definition', $json['data']['page']['blocks']['test-region'][0]);
        $this->assertEquals($block->definition_name, $json['data']['page']['blocks']['test-region'][0]['definition']['name']);
    }

}
