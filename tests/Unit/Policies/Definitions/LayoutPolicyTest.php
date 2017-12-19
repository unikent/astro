<?php
namespace Tests\Policies;

use Tests\Unit\PolicyTestCase;
use Astro\API\Models\Definitions\Layout as LayoutDefinition;
use Astro\API\Policies\Definitions\LayoutPolicy as LayoutDefinitionPolicy;

class LayoutPolicyTest extends PolicyTestCase
{

    /**
     * @test
     * @group authorization
     */
    public function index_IsAllowed(){
        $user = $this->getUser();
        $this->assertPolicyAllows(new LayoutDefinitionPolicy, 'index', $user);
    }

    /**
     * @test
     * @group authorization
     */
    public function read_IsAllowed(){
        $user = $this->getUser();
        $definition = new LayoutDefinition;

        $this->assertPolicyAllows(new LayoutDefinitionPolicy, 'read', $user, $definition);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_IsDenied(){
        $user = $this->getUser();
        $this->assertPolicyDenies(new LayoutDefinitionPolicy, 'create', $user);
    }

    /**
     * @test
     * @group authorization
     */
    public function update_IsDenied(){
        $user = $this->getUser();
        $definition = new LayoutDefinition;

        $this->assertPolicyDenies(new LayoutDefinitionPolicy, 'update', $user, $definition);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_IsDenied(){
        $user = $this->getUser();
        $definition = new LayoutDefinition;

        $this->assertPolicyDenies(new LayoutDefinitionPolicy, 'delete', $user, $definition);
    }

}
