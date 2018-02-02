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
        $response = $this->action('GET', RegionController::class . '@definition', 'test-region-v1');
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
        $this->action('GET', RegionController::class . '@definition', 'test-region-v1');
    }

    /**
     * @test
     * @group authorization
     */
    public function definition_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', RegionController::class . '@definition', 'test-region-v1');
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function definition_WhenAuthorized_Returns200(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@definition', 'test-region-v1');
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function definition_WhenAuthorizedAndFound_ReturnsJson(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@definition', 'test-region-v1');

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
		$this->assertEquals('test-region', $json['data']['name']);
		$this->assertEquals(1, $json['data']['version']);
    }

    /**
     * @test
     */
    public function definition_WhenAuthorizedAndFoundAndRequestIncludesBlockDefinitions_IncludesBlockDefinitionsInJson(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', RegionController::class . '@definition', [
            'layout_definition' => 'test-region-v1',
            'include' => 'block_definitions',
        ]);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('block_definitions', $json['data']);
        $this->assertCount(1, $json['data']['block_definitions']);
    }

}

