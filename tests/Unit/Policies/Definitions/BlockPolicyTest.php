<?php
namespace Tests\Policies;

use Tests\Unit\PolicyTestCase;
use Astro\API\Models\Definitions\Block as BlockDefinition;
use App\Policies\Definitions\BlockPolicy as BlockDefinitionPolicy;

class BlockPolicyTest extends PolicyTestCase
{

    /**
     * @test
     * @group authorization
     */
    public function index_IsAllowed(){
        $user = $this->getUser();
        $this->assertPolicyAllows(new BlockDefinitionPolicy, 'index', $user);
    }

    /**
     * @test
     * @group authorization
     */
    public function read_IsAllowed(){
        $user = $this->getUser();
        $definition = new BlockDefinition;

        $this->assertPolicyAllows(new BlockDefinitionPolicy, 'read', $user, $definition);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_IsDenied(){
        $user = $this->getUser();
        $this->assertPolicyDenies(new BlockDefinitionPolicy, 'create', $user);
    }

    /**
     * @test
     * @group authorization
     */
    public function update_IsDenied(){
        $user = $this->getUser();
        $definition = new BlockDefinition;

        $this->assertPolicyDenies(new BlockDefinitionPolicy, 'update', $user, $definition);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_IsDenied(){
        $user = $this->getUser();
        $definition = new BlockDefinition;

        $this->assertPolicyDenies(new BlockDefinitionPolicy, 'delete', $user, $definition);
    }

}
