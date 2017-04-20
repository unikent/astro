<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use App\Models\Definitions\Region as Definition;
use App\Http\Controllers\Api\v1\RegionController;

class RegionControllerTest extends ApiControllerTestCase {

    /**
     * @test
     * @group authentication
     */
    public function definitions_WhenUnauthenticated_Returns403(){
        $response = $this->action('GET', RegionController::class . '@definitions');
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function definitions_WhenAuthenticated_ChecksAuthorization(){
        Gate::shouldReceive('authorize')->with('index', Definition::class)->once();

        $this->authenticated();
        $this->action('GET', RegionController::class . '@definitions');
    }

    /**
     * @test
     * @group authorization
     */
    public function definitions_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', RegionController::class . '@definitions');
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function definitions_WhenAuthorized_Returns200(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@definitions');
        $response->assertStatus(200);
    }



    /**
     * @test
     * @group authentication
     */
    public function definition_WhenUnauthenticated_Returns403(){
        $response = $this->action('GET', RegionController::class . '@definition', 'test-region');
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function definition_WhenAuthenticatedButNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@definition', 'foobar');
        $response->assertStatus(404);
    }

    /**
     * @test
     * @group authorization
     */
    public function definition_WhenAuthenticatedAndFound_ChecksAuthorization(){
        Gate::shouldReceive('authorize')->with('read', Definition::class)->once();

        $this->authenticated();
        $this->action('GET', RegionController::class . '@definition', 'test-region');
    }

    /**
     * @test
     * @group authorization
     */
    public function definition_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', RegionController::class . '@definition', 'test-region');
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function definition_WhenAuthorized_Returns200(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@definition', 'test-region');
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function definition_WhenAuthorized_ReturnsJson(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@definition', 'test-region');

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals('test-region', $json['data']['name']);
    }



    /**
     * @test
     * @group authentication
     */
    public function blocks_WhenUnauthenticated_Returns403(){
        $response = $this->action('GET', RegionController::class . '@blocks', 'test-region');
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function blocks_WhenAuthenticatedButNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@blocks', 'foobar');
        $response->assertStatus(404);
    }

    /**
     * @test
     * @group authorization
     */
    public function blocks_WhenAuthenticatedAndFound_ChecksAuthorization(){
        Gate::shouldReceive('authorize')->with('read', Definition::class)->once();

        $this->authenticated();
        $this->action('GET', RegionController::class . '@blocks', 'test-region');
    }

    /**
     * @test
     * @group authorization
     */
    public function blocks_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', RegionController::class . '@blocks', 'test-region');
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function blocks_WhenAuthorized_Returns200(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@blocks', 'test-region');
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function blocks_WhenAuthorized_ReturnsJson(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@blocks', 'test-region');

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertCount(1, $json['data']);
        $this->assertEquals('test-block', $json['data'][0]['name']);
    }

}

