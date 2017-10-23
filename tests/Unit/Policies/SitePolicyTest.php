<?php
namespace Tests\Policies;

use App\Models\User;
use App\Models\Site;
use App\Policies\SitePolicy;
use Tests\Unit\PolicyTestCase;
use App\Models\PublishingGroup;

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
    public function update_WhenUserIsNotMemberOfSitePublishingGroup_IsDenied(){
    	// Create some PracticeGroups...
    	$pgs = factory(PublishingGroup::class, 2)->create();

    	// ...site is linked to PracticeGroup '0'...
    	$site = factory(Site::class)->create([ 'publishing_group_id' => $pgs[0]->getKey() ]);

    	// ...but the User is a member of PracticeGroup '1'...
    	$user = factory(User::class)->create();
    	$user->publishing_groups()->attach($pgs[1]);

    	// ...denied!
        $this->assertPolicyDenies(new SitePolicy, 'update', $user, $site);
    }

    /**
     * @test
     * @group authorization
     */
    public function update_WhenUserIsMemberOfSitePublishingGroup_IsAllowed(){
    	return $this->markTestIncomplete();
    	// Create some PracticeGroups...
    	$pg = factory(PublishingGroup::class)->create();

    	// ...site is linked to PracticeGroup '0'...
    	$site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);

    	// ...but the User is a member of PracticeGroup '1'...
    	$user = factory(User::class)->create();
    	$user->publishing_groups()->attach($pg);

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
