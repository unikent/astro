<?php
namespace Tests\Policies;

use App\Models\User;
use App\Models\Site;
use App\Models\Media;
use Tests\FileUploadTrait;
use Tests\FileCleanupTrait;
use App\Policies\MediaPolicy;
use Tests\Unit\PolicyTestCase;
use App\Models\PublishingGroup;

class MediaPolicyTest extends PolicyTestCase
{

    use FileUploadTrait, FileCleanupTrait;


    /**
     * @test
     * @group authorization
     */
    public function index_WhenUserIsUnprivilegedAndAcoIsMediaClass_IsDenied(){
        $user = factory(User::class)->make([ 'role' => 'user' ]);
        $this->assertPolicyDenies(new MediaPolicy, 'index', $user, Media::class);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WhenUserIsAdminAndAcoIsMediaClass_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $this->assertPolicyAllows(new MediaPolicy, 'index', $user, Media::class);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WhenUserIsAdminAndAcoIsArrayWithPublishingGroup_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $pg = factory(PublishingGroup::class)->create();

        $this->assertPolicyAllows(new MediaPolicy, 'index', $user, [ Media::class, $pg ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WhenUserIsAdminAndAcoIsArrayWithSite_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $site = factory(Site::class)->states('withPublishingGroup')->create();

        $this->assertPolicyAllows(new MediaPolicy, 'index', $user, [ Media::class, $site ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WithPublishingGroupAndUserIsNotMember_IsDenied(){
        $user = factory(User::class)->create([ 'role' => 'user' ]);
        $pg = factory(PublishingGroup::class)->create();

        $this->assertPolicyDenies(new MediaPolicy, 'index', $user, [ Media::class, $pg ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WithPublishingGroupAndUserIsMember_IsAllowed(){
        $user = factory(User::class)->create([ 'role' => 'user' ]);

        $pg = factory(PublishingGroup::class)->create();
        $pg->users()->attach($user);

        $this->assertPolicyAllows(new MediaPolicy, 'index', $user, [ Media::class, $pg ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WithSiteAndUserIsNotMemberOfPublishingGroup_IsDenied(){
        $user = factory(User::class)->create([ 'role' => 'user' ]);
        $site = factory(Site::class)->states('withPublishingGroup')->create();

        $this->assertPolicyDenies(new MediaPolicy, 'index', $user, [ Media::class, $site ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function index_WithSiteAndUserIsMemberOfPublishingGroup_IsAllowed(){
        $user = factory(User::class)->create([ 'role' => 'user' ]);

        $site = factory(Site::class)->states('withPublishingGroup')->create();
        $site->publishing_group->users()->attach($user);

        $this->assertPolicyAllows(new MediaPolicy, 'index', $user, [ Media::class, $site ]);
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
    public function create_WhenUserIsAdminAndAcoIsOnlyMedia_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'create', $user, [ $media ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_WhenUserIsAdminAndAcoIsMediaAndPublishingGroup_IsAllowed(){
        $pg = factory(PublishingGroup::class)->create();

        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'create', $user, [ $media, $pg ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_WhenUserIsAdminAndAcoIsMediaAndSite_IsAllowed(){
        $site = factory(Site::class)->states('withPublishingGroup')->create();

        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'create', $user, [ $media, $site ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_WhenAcoDoesNotIncludeSiteOrPublishingGroup_IsDenied(){
    	$user = factory(User::class)->create();
    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

    	$this->assertPolicyDenies(new MediaPolicy, 'create', $user, [ $media ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_WhenAcoIncludesPublishingGroupAndUserIsNotInPublishingGroup_IsDenied(){
    	$pg = factory(PublishingGroup::class)->create();

    	$user = factory(User::class)->create();

    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
    	$media->publishing_groups()->attach($pg);

    	$this->assertPolicyDenies(new MediaPolicy, 'create', $user, [ $media, $pg ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_WhenAcoIncludesPublishingGroupAndUserIsInPublishingGroup_IsAllowed(){
    	$pg = factory(PublishingGroup::class)->create();

    	$user = factory(User::class)->create();
    	$user->publishing_groups()->attach($pg);

    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
    	$media->publishing_groups()->attach($pg);

    	$this->assertPolicyAllows(new MediaPolicy, 'create', $user, [ $media, $pg ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_WhenAcoIncludesSiteAndUserIsNotInPublishingGroup_IsDenied(){
    	$pg = factory(PublishingGroup::class)->create();

    	$user = factory(User::class)->create();

    	$site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);

    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
    	$media->sites()->attach($site);

    	$this->assertPolicyDenies(new MediaPolicy, 'create', $user, [ $media, $site ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function create_WhenAcoIncludesSiteAndUserIsInPublishingGroup_IsAllowed(){
    	$pg = factory(PublishingGroup::class)->create();

    	$user = factory(User::class)->create();
    	$user->publishing_groups()->attach($pg);

    	$site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);

    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
    	$media->sites()->attach($site);

    	$this->assertPolicyAllows(new MediaPolicy, 'create', $user, [ $media, $site ]);
    }



    /**
     * @test
     * @group authorization
     */
    public function update_IsAllowed(){
    	$this->markTestIncomplete();
    }



    /**
     * @test
     * @group authorization
     */
    public function delete_WhenUserIsAdminAndAcoIsOnlyMedia_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'delete', $user, [ $media ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_WhenUserIsAdminAndAcoIsMediaAndPublishingGroup_IsAllowed(){
        $pg = factory(PublishingGroup::class)->create();

        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'delete', $user, [ $media, $pg ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_WhenUserIsAdminAndAcoIsMediaAndSite_IsAllowed(){
        $site = factory(Site::class)->states('withPublishingGroup')->create();

        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'delete', $user, [ $media, $site ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_WhenAcoDoesNotIncludeSiteOrPublishingGroup_IsDenied(){
    	$user = factory(User::class)->create();
    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

    	$this->assertPolicyDenies(new MediaPolicy, 'delete', $user, [ $media ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_WhenAcoIncludesPublishingGroupAndUserIsNotInPublishingGroup_IsDenied(){
    	$pg = factory(PublishingGroup::class)->create();

    	$user = factory(User::class)->create();

    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
    	$media->publishing_groups()->attach($pg);

    	$this->assertPolicyDenies(new MediaPolicy, 'delete', $user, [ $media, $pg ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_WhenAcoIncludesPublishingGroupAndUserIsInPublishingGroup_IsAllowed(){
    	$pg = factory(PublishingGroup::class)->create();

    	$user = factory(User::class)->create();
    	$user->publishing_groups()->attach($pg);

    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
    	$media->publishing_groups()->attach($pg);

    	$this->assertPolicyAllows(new MediaPolicy, 'delete', $user, [ $media, $pg ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_WhenAcoIncludesSiteAndUserIsNotInPublishingGroup_IsDenied(){
    	$pg = factory(PublishingGroup::class)->create();

    	$user = factory(User::class)->create();

    	$site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);

    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
    	$media->sites()->attach($site);

    	$this->assertPolicyDenies(new MediaPolicy, 'delete', $user, [ $media, $site ]);
    }

    /**
     * @test
     * @group authorization
     */
    public function delete_WhenAcoIncludesSiteAndUserIsInPublishingGroup_IsAllowed(){
    	$pg = factory(PublishingGroup::class)->create();

    	$user = factory(User::class)->create();
    	$user->publishing_groups()->attach($pg);

    	$site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);

    	$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
    	$media->sites()->attach($site);

    	$this->assertPolicyAllows(new MediaPolicy, 'delete', $user, [ $media, $site ]);
    }

}
