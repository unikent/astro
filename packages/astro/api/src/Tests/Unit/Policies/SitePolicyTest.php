<?php
namespace Astro\API\Tests\Policies;

use App\Models\User;
use Astro\API\Models\Site;
use Astro\API\Policies\SitePolicy;
use Astro\API\Tests\Unit\PolicyTestCase;

class SitePolicyTest extends PolicyTestCase
{

    /**
     * @test
     * @group authorization
     */
    public function index_WhenUserIsAdmin_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $this->assertPolicyAllows(new SitePolicy, 'index', $user, Site::class);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WhenUserIsNotAdmin_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'user' ]);
        $this->assertPolicyAllows(new SitePolicy, 'index', $user, Site::class);
    }



    /**
     * @test
     * @group authorization
     */
    public function read_IsAllowed(){
    	$this->markTestIncomplete();
    }



    /**
     * @test
     * @group authorization
     */
    public function create_IsAllowed(){
    	$this->markTestIncomplete();
    }



    /**
     * @test
     * @group authorization
     */
    public function update_WhenUserIsNotMemberOfSite_IsDenied(){
    	return $this->markTestIncomplete();

    	$user = factory(User::class)->create();

    	// ...denied!
        $this->assertPolicyDenies(new SitePolicy, 'update', $user, $site);
    }

    /**
     * @test
     * @group authorization
     */
    public function update_WhenUserIsMemberOfSitePublishingGroup_IsAllowed(){
    	return $this->markTestIncomplete();

    	// ...denied!
        $this->assertPolicyAllows(new SitePolicy, 'update', $user, $site);
    }



    /**
     * @test
     * @group authorization
     */
    public function delete_IsDenied(){
    	$this->markTestIncomplete();
    }
}
