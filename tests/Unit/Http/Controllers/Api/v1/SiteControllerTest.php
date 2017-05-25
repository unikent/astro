<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use Mockery;
use App\Models\Site;
use App\Models\Route;
use App\Http\Controllers\Api\v1\SiteController;
use App\Http\Transformers\Api\v1\PageTransformer;
use Illuminate\Auth\Access\AuthorizationException;

class SiteControllerTest extends ApiControllerTestCase {

    /**
     * @test
     * @group authentication
     */
    public function index_WhenUnauthenticated_Returns403(){
        $response = $this->action('GET', SiteController::class . '@index');
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WhenAuthenticated_ChecksAuthorization(){
        Gate::shouldReceive('authorize')->with('index', Site::class)->once();

        $this->authenticated();
        $this->action('GET', SiteController::class . '@index');
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', SiteController::class . '@index');
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function index_WhenAuthorized_Returns200(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@index');
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function index_WhenAuthorizedAndFound_ReturnsJson(){
        $parent = factory(Route::class)->states([ 'isRoot', 'withPage' ])->create();
        $parent->page->publish(new PageTransformer);

        $routes = factory(Route::class, 3)->states([ 'withPage', 'withSite' ])->create([ 'parent_id' => $parent->getKey() ])
            ->each(function($route){
                $route->page->publish(new PageTransformer);
            });

        $this->authenticatedAndAuthorized();

        $count = Site::count();

        $response = $this->action('GET', SiteController::class . '@index');
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertCount($count, $json['data']);
    }

    /**
     * @test
     */
    public function index_WhenAuthorizedAndFound_ReturnsActiveRoutesInJson(){
        $parent = factory(Route::class)->states([ 'isRoot', 'withPage' ])->create();
        $parent->page->publish(new PageTransformer);

        $routes = factory(Route::class, 3)->states([ 'withPage', 'withSite' ])->create([ 'parent_id' => $parent->getKey() ])
            ->each(function($route){
                $route->page->publish(new PageTransformer);
            });

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@index');
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('active_route', $json['data'][0]);
    }



    /**
     * @test
     * @group authentication
     */
    public function show_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        $response = $this->action('GET', SiteController::class . '@show', [ $site->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function show_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        Gate::shouldReceive('authorize')->with('read', Mockery::type(Site::class))->once();

        $this->authenticated();
        $response = $this->action('GET', SiteController::class . '@show', [ $site->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function show_WhenAuthenticatedAndUnauthorized_Returns403(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', SiteController::class . '@show', [ $site->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndPageNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@show', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFound_Returns200(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@show', [ $site->getKey() ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFound_ReturnsJsonOfSite(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@show', [ $site->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertEquals($site->name, $json['data']['name']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFound_ReturnsActiveRouteInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@show', [ $site->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('active_route', $json['data']);
        $this->assertEquals($route->slug, $json['data']['active_route']['slug']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesRoutes_IncludesRoutesInJson(){
        $active = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $active->page->publish(new PageTransformer);

        $draft = factory(Route::class)->create(array_except(attrs_for($active), [ 'id', 'is_active' ]));

        $site = $active->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@show', [
            'site' => $site->getKey(),
            'include' => 'routes',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('routes', $json['data']);
        $this->assertCount(2, $json['data']['routes']);
    }



    /**
     * @test
     * @group authentication
     */
    public function tree_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        $response = $this->action('GET', SiteController::class . '@tree', [ $site->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function tree_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        Gate::shouldReceive('authorize')->with('read', Mockery::type(Site::class))->once();

        $this->authenticated();
        $response = $this->action('GET', SiteController::class . '@tree', [ $site->getKey() ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function tree_WhenAuthenticatedAndUnauthorized_Returns403(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', SiteController::class . '@tree', [ $site->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function tree_WhenAuthorizedAndNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@tree', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function tree_WhenAuthorizedAndFound_Returns200(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $site = $route->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@tree', [ $site->getKey() ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function tree_WhenAuthorizedAndFound_ReturnsJsonOfSiteRoutesAsHierarchy(){
        $route = factory(Route::class)->states([ 'withPage', 'isRoot', 'withSite' ])->create();
        $route->page->publish(new PageTransformer);

        $l1 = factory(Route::class, 2)->states([ 'withPage' ])->create([ 'parent_id' => $route->getKey() ])->each(function($r){
            $r->page->publish(new PageTransformer);
        });

        $l2 = factory(Route::class, 2)->states([ 'withPage' ])->create([ 'parent_id' => $l1[0]->getKey() ])->each(function($r){
            $r->page->publish(new PageTransformer);
        });

        $site = $route->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@tree', [ $site->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertEquals($route->slug, $json['data'][0]['slug']);

        $this->assertEquals($l1[0]->slug, $json['data'][0]['children'][0]['slug']);
        $this->assertEquals($l2[0]->slug, $json['data'][0]['children'][0]['children'][0]['slug']);
        $this->assertEquals($l2[1]->slug, $json['data'][0]['children'][0]['children'][1]['slug']);

        $this->assertEquals($l1[1]->slug, $json['data'][0]['children'][1]['slug']);
    }


}
