<?php
namespace Tests\Unit;

use Faker;
use Mockery;
use Tests\TestCase;
use App\Models\User;

abstract class PolicyTestCase extends TestCase
{

    public function getUser($states = []){
        if(!empty($states)){
            return factory(User::class)->states($states)->make();
        }

        return factory(User::class)->make();
    }

    public function getAdmin($states = []){
        $this->getUser();
    }

    public function assertPolicyAllows($policy, $action, $aro, $aco = null){
        if(method_exists($policy, 'before')){
            $this->assertTrue( ($policy->before($aro, $aco) || $policy->{$action}($aro, $aco)) );
        } else {
            $this->assertTrue($policy->{$action}($aro, $aco));
        }
    }

    public function assertPolicyDenies($policy, $action, $aro, $aco = null){
        if(method_exists($policy, 'before')){
            $this->assertFalse( ($policy->before($aro, $aco) || $policy->{$action}($aro, $aco)) );
        } else {
            $this->assertFalse($policy->{$action}($aro, $aco));
        }
    }

}
