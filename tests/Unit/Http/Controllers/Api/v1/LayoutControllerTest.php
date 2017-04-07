<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use App\Models\Definitions\Layout as Definition;
use App\Http\Controllers\Api\v1\LayoutController;

class MessageControllerTest extends ApiControllerTestCase {

    /**
     * @test
     * @group authentication
     */
    public function definitions_WhenUnauthenticated_Returns403(){
        $response = $this->action('GET', LayoutController::class . '@definitions');
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function definitions_WhenAuthenticated_ChecksAuthorization(){
        Gate::shouldReceive('authorize')->with('index', Definition::class)->once();

        $this->authenticated();
        $this->action('GET', LayoutController::class . '@definitions');
    }

    /**
     * @test
     * @group authorization
     */
    public function definitions_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('GET', LayoutController::class . '@definitions');
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function definitions_WhenAuthorized_Returns200(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', LayoutController::class . '@definitions');
        $response->assertStatus(200);
    }

}

