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
        $route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
        $route->page->publish(new PageTransformer);

        $page = $route->page;
        $page->publish(new PageTransformer);

        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndRouteNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => '/foobar' ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     * @group authorization
     */
    public function resolve_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
        $route->page->publish(new PageTransformer);

        $page = $route->page;
        $page->publish(new PageTransformer);

        Gate::shouldReceive('allows')->with('read', Mockery::type(Route::class))->once();

        $this->authenticated();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function resolve_WhenAuthenticatedAndUnauthorized_Returns404(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
        $route->page->publish(new PageTransformer);

        $page = $route->page;
        $page->publish(new PageTransformer);

        $this->authenticatedAndUnauthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundAndNotPublished_Returns200(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
        $route->page->publish(new PageTransformer);

        $page = $route->page;
        $page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundAndNotPublished_ReturnsJson(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
        $route->page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals($route->page->id, $json['data']['id']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFoundAndPublished_Returns200(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
        $route->page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFound_ReturnsJson(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
        $route->page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals($route->page->id, $json['data']['id']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFound_IncludesActiveRouteInJson(){
        $active = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
        $active->page->publish(new PageTransformer);

        sleep(1);

        $draft = factory(Route::class)->create(array_except(attrs_for($active), [ 'id', 'is_active' ]));

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $active->path ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('active_route', $json['data']);
        $this->assertEquals($active->slug, $json['data']['active_route']['slug']);
    }

    /**
     * @test
     */
    public function resolve_WhenAuthorizedAndFound_IncludesPageBlocksByRegionInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot' ])->create();
        $block = factory(Block::class)->create([ 'page_id' => $route->page->getKey() ]);

        $route->page->publish(new PageTransformer);

        $this->authenticatedAndAuthorized();
        $response = $this->action('GET', RouteController::class . '@resolve', [ 'path' => $route->path ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('blocks', $json['data']);
        $this->assertArrayHasKey('test-region', $json['data']['blocks']);
        $this->assertCount(1, $json['data']['blocks']);
    }

}
