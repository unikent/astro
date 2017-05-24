<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use Mockery;
use App\Models\Site;
use App\Models\User;
use App\Models\Route;
use App\Models\PublishingGroup;
use App\Http\Controllers\Api\v1\SiteController;
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
        Gate::shouldReceive('allows')->with('index', Site::class)->once();

        $this->authenticated();
        $this->action('GET', SiteController::class . '@index');
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WhenAuthenticatedAndUnauthorizedToIndex_Returns200(){
        $this->authenticated();
        Gate::shouldReceive('allows')->with('index', Site::class)->andReturn(false); // Not Admin

        $response = $this->action('GET', SiteController::class . '@index');
        $response->assertStatus(200);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WhenAuthenticatedAndNotAuthorizedToIndex_ReturnsJsonOfSitePagesAssociatedWithUser(){
        $routes = factory(Route::class, 3)->states([ 'withPage', 'withParent', 'withSite' ])->create([ 'is_canonical' => true ]);

        // Create a PublishingGroup...
        $pg = factory(PublishingGroup::class)->create();

        // ...associate the Site with the PG...
        $routes[1]->site->publishing_group_id = $pg->getKey();
        $routes[1]->site->save();

        // ...and associate the User with the PG...
        $user = factory(User::class)->create([ 'role' => 'user' ]);
        $user->publishing_groups()->attach($pg);

        $this->authenticated($user);
        Gate::shouldReceive('allows')->with('index', Site::class)->andReturn(false); // Not Admin

        $response = $this->action('GET', SiteController::class . '@index');
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);

        $this->assertCount(1, $json['data']);
        $this->assertEquals($routes[1]->site->getKey(), $json['data'][0]['id']);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WhenAuthorizedToIndex_Returns200(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@index');
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function index_WhenAuthorizedToIndex_ReturnsJsonOfAllSitePages(){
        $routes = factory(Route::class, 3)->states([ 'withPage', 'withParent', 'withSite' ])->create([ 'is_canonical' => true ]);

        factory(Route::class)->create([
            'parent_id' => $routes[0]->parent_id,
            'page_id' => $routes[0]->page_id,
            'site_id' => $routes[0]->site_id,
        ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@index');
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertCount(3, $json['data']);
    }

    /**
     * @test
     */
    public function index_WhenAuthorized_ReturnsCanonicalRoutesInJson(){
        $routes = factory(Route::class, 3)->states([ 'withPage', 'withParent', 'withSite' ])->create([ 'is_canonical' => true ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@index');
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('canonical', $json['data'][0]);
    }



    /**
     * @test
     * @group authentication
     */
    public function show_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create();
        $site = $route->site;

        $response = $this->action('GET', SiteController::class . '@show', [ $site->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function show_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create();
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
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create();
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
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create();
        $site = $route->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@show', [ $site->getKey() ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFound_ReturnsJsonOfSite(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create();
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
    public function show_WhenAuthorizedAndFound_ReturnsCanonicalRouteInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create();
        $route->makeCanonical();

        $site = $route->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@show', [ $site->getKey() ]);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('canonical', $json['data']);
        $this->assertEquals($route->slug, $json['data']['canonical']['slug']);
    }

    /**
     * @test
     */
    public function show_WhenAuthorizedAndFoundRequestIncludesRoutes_IncludesRoutesInJson(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create();
        $route->makeCanonical();

        $routes = factory(Route::class, 2)->create([
            'site_id' => $route->site_id,
            'page_id' => $route->page_id,
            'parent_id' => $route->parent_id,
        ]);

        $site = $route->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@show', [
            'site' => $site->getKey(),
            'include' => 'routes',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('routes', $json['data']);
        $this->assertCount(3, $json['data']['routes']);
    }



    /**
     * @test
     * @group authentication
     */
    public function tree_WhenUnauthenticated_Returns401(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create([ 'is_canonical' => true ]);
        $site = $route->site;

        $response = $this->action('GET', SiteController::class . '@tree', [ $site->getKey() ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function tree_WhenAuthenticated_ChecksAuthorization(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create([ 'is_canonical' => true ]);
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
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create([ 'is_canonical' => true ]);
        $site = $route->site;

        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', SiteController::class . '@tree', [ $site->getKey() ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function tree_WhenAuthorizedAndPageNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@tree', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function tree_WhenAuthorizedAndFound_Returns200(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create([ 'is_canonical' => true ]);
        $site = $route->site;

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', SiteController::class . '@tree', [ $site->getKey() ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function tree_WhenAuthorizedAndFound_ReturnsJsonOfSiteRoutesAsHierarchy(){
        $route = factory(Route::class)->states([ 'withPage', 'withParent', 'withSite' ])->create([ 'is_canonical' => true ]);

        $l1 = factory(Route::class, 2)->states([ 'withPage' ])->create([ 'parent_id' => $route->getKey(), 'is_canonical' => true ]);
        $l2 = factory(Route::class, 2)->states([ 'withPage' ])->create([ 'parent_id' => $l1[0]->getKey(), 'is_canonical' => true ]);

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
