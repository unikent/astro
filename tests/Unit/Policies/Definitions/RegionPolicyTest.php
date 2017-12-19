<?php
namespace Tests\Policies;

use Tests\Unit\PolicyTestCase;
use Astro\API\Models\Definitions\Region as RegionDefinition;
use Astro\API\Policies\Definitions\RegionPolicy as RegionDefinitionPolicy;

class RegionPolicyTest extends PolicyTestCase
{

    /**
     * @test
     * @group authorization
     */
    public function index_IsAllowed(){
        $user = $this->getUser();
        $this->assertPolicyAllows(new RegionDefinitionPolicy, 'index', $user);
    }

    /**
     * @test
     * @group authorization
     */
    public function read_IsAllowed(){
        $user = $this->getUser();
        $definition = new RegionDefinition;

        $this->assertPolicyAllows(new RegionDefinitionPolicy, 'read', $user, $definition);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_IsDenied(){
        $user = $this->getUser();
        $this->assertPolicyDenies(new RegionDefinitionPolicy, 'create', $user);
    }

    /**
     * @test
     * @group authorization
     */
    public function update_IsDenied(){
        $user = $this->getUser();
        $definition = new RegionDefinition;

        $this->assertPolicyDenies(new RegionDefinitionPolicy, 'update', $user, $definition);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_IsDenied(){
        $user = $this->getUser();
        $definition = new RegionDefinition;

        $this->assertPolicyDenies(new RegionDefinitionPolicy, 'delete', $user, $definition);
    }

}
