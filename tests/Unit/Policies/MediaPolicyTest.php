<?php
namespace Tests\Policies;

use App\Models\User;
use Astro\API\Models\Site;
use Astro\API\Models\Media;
use Tests\FileUploadTrait;
use Tests\FileCleanupTrait;
use App\Policies\MediaPolicy;
use Tests\Unit\PolicyTestCase;

class MediaPolicyTest extends PolicyTestCase
{

    use FileUploadTrait, FileCleanupTrait;

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function index_WhenUserIsUnprivilegedAndAcoIsMediaClass_IsDenied(){
        $user = factory(User::class)->make([ 'role' => 'user' ]);
        $this->assertPolicyDenies(new MediaPolicy, 'index', $user, Media::class);
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function index_WhenUserIsAdminAndAcoIsMediaClass_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $this->assertPolicyAllows(new MediaPolicy, 'index', $user, Media::class);
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function index_WhenUserIsAdminAndAcoIsArrayWithSite_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $site = factory(Site::class)->create();

        $this->assertPolicyAllows(new MediaPolicy, 'index', $user, [ Media::class, $site ]);
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function read_IsAllowed(){
    	$this->markTestIncomplete();
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function create_WhenUserIsAdminAndAcoIsOnlyMedia_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'create', $user, [ $media ]);
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function create_WhenUserIsAdminAndAcoIsMediaAndSite_IsAllowed(){
        $site = factory(Site::class)->create();

        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'create', $user, [ $media, $site ]);
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function update_IsAllowed(){
    	$this->markTestIncomplete();
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function delete_WhenUserIsAdminAndAcoIsOnlyMedia_IsAllowed(){
        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'delete', $user, [ $media ]);
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function delete_WhenUserIsAdminAndAcoIsMediaAndSite_IsAllowed(){
        $site = factory(Site::class)->create();

        $user = factory(User::class)->make([ 'role' => 'admin' ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $this->assertPolicyAllows(new MediaPolicy, 'delete', $user, [ $media, $site ]);
    }

}
