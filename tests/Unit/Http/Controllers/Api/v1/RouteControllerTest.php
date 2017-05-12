<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use Mockery;
use App\Models\Block;
use App\Models\Route;
use App\Http\Controllers\Api\v1\RouteController;
use App\Http\Transformers\Api\v1\PageTransformer;
use Illuminate\Auth\Access\AuthorizationException;

class RouteControllerTest extends ApiControllerTestCase {

    /**
     * @test
     * @group authentication
     */
    public function resolve_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function resolve_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

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

        $page = $route->page;
        $page->publish(new PageTransformer);

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

        $page = $route->page;
        $page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFound_ReturnsJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $page = $route->page;
        $page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals($route->page->id, $json['data']['id']);
    }

    /**
     * @test
     * @group focus
     */
    public function resolve_WhenAuthorizedAndFound_IncludesCanonicalRouteInJson(){
        $r1 = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();
        $r1->page->publish(new PageTransformer);

        sleep(1);

        $r2 = factory(Route::class)->create([ 'page_id' => $r1->page_id, 'parent_id' => $r1->parent_id ]);
        $r2->page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $r1->path ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('canonical', $json['data']);
        $this->assertEquals($r2->slug, $json['data']['canonical']['slug']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFound_IncludesPageBlocksByRegionInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent' ])->create();

        $page = $route->page;
        $block = factory(Block::class)->create([ 'page_id' => $page->getKey() ]);

        $page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('blocks', $json['data']);
        $this->assertArrayHasKey('test-region', $json['data']['blocks']);
        $this->assertCount(1, $json['data']['blocks']);
    }

}
