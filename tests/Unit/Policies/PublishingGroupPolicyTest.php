<?php
namespace Tests\Policies;

use App\Models\User;
use App\Models\PublishingGroup;
use App\Policies\PublishingGroupPolicy;
use Tests\Unit\PolicyTestCase;

class PublishingGroupPolicyTest extends PolicyTestCase
{

    /**
     * @test
     * @group authorization
     */
    public function index_WhenUserIsAdmin_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $this->assertPolicyAllows(new PublishingGroupPolicy, 'index', $user, PublishingGroup::class);
    }

    /**
     * @test
     * @group authorization
     * 
     */ 
    public function index_WhenUserIsNotAdmin_IsDenied(){
        // $user = factory(User::class)->make([ 'role' => 'user' ]);
        // $this->assertPolicyDenies(new PublishingGroupPolicy, 'index', $user, PublishingGroup::class);
        $this->markTestIncomplete('This requires further work on permission model');
    }

}
