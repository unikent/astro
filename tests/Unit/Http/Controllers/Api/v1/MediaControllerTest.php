<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use Mockery;
use App\Models\Site;
use App\Models\Media;
use Tests\FileUploadTrait;
use Tests\FileCleanupTrait;
use App\Models\PublishingGroup;
use App\Http\Controllers\Api\v1\MediaController;
use Illuminate\Auth\Access\AuthorizationException;

class MediaControllerTest extends ApiControllerTestCase {

    use FileUploadTrait, FileCleanupTrait;


    public function getAttrs(PublishingGroup $pg = null, Site $site = null)
    {
        $pg = $pg ?: factory(PublishingGroup::class)->create();
        $site = $site ?: factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);

        return [
            'upload' => $this->setupFileUpload('media', 'image.jpg'),

            'site_ids' => [ $site->getKey() ],
            'publishing_group_ids' => [ $pg->getKey() ],
        ];
    }


    /**
     * @test
     * @group authentication
     */
    public function store_WhenUnauthenticated_Returns401(){
        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthenticated_ChecksAuthorizationForSite(){
        Gate::shouldReceive('authorize')->with('create', Mockery::on(function($args){
            return (is_array($args) && is_a($args[0], Media::class) && is_a($args[1], Site::class));
        }))->once();

        Gate::shouldReceive('authorize')->with('create', Mockery::any())->andReturn(true);

        $this->authenticated();
        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthenticated_ChecksAuthorizationForPublishingGroup(){
        Gate::shouldReceive('authorize')->with('create', Mockery::on(function($args){
            return (is_array($args) && is_a($args[0], Media::class) && is_a($args[1], PublishingGroup::class));
        }))->once();

        Gate::shouldReceive('authorize')->with('create', Mockery::any())->andReturn(true);

        $this->authenticated();
        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());
    }

    /**
     * @test
     * @group authorization
     */
    public function store_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValidAndMediaDoesNotExist_CreatesMedia(){
        $count = Media::count();

        $this->authenticatedAndAuthorized();
        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());

        $this->assertEquals($count+1, Media::count());
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValidAndMediaDoesNotExist_AssociatesMediaWithSiteAndPublishingGroup(){
        $this->authenticatedAndAuthorized();

        $pg = factory(PublishingGroup::class)->create();
        $site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);

        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs($pg, $site));

        $media = Media::all()->last();
        $this->assertContains($pg->getKey(), $media->publishing_groups->pluck('id'));
        $this->assertContains($site->getKey(), $media->sites->pluck('id'));
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValidAndMediaAlreadyExists_DoesNotCreateNewMedia(){
        // Set up the existing Media item
        $pg = factory(PublishingGroup::class)->create();
        $site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);

        $media = factory(Media::class)->create([
            'file' => $this->setupFileUpload('media', 'image.jpg')
        ]);

        $media->sites()->sync([ $site->getKey() ]);
        $media->publishing_groups()->sync([ $pg->getKey() ]);

        // And now set up our attributes, essentially a duplicate file
        $pg2 = factory(PublishingGroup::class)->create();
        $site2 = factory(Site::class)->create([ 'publishing_group_id' => $pg2->getKey() ]);

        $attrs = [
            'upload' => $this->setupFile('media', 'image.jpg'),

            'site_ids' => [ $site2->getKey() ],
            'publishing_group_ids' => [ $pg2->getKey() ],
        ];

        // Run the request, assert that the count does not increased
        $count = Media::count();

        $this->authenticatedAndAuthorized();
        $response = $this->action('POST', MediaController::class . '@store', [], $attrs);

        $this->assertEquals($count, Media::count());
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValidAndMediaAlreadyExists_AssociatesExisitngMediaWithSiteAndPublishingGroup(){
        // Set up the existing Media item
        $pg = factory(PublishingGroup::class)->create();
        $site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);

        $media = factory(Media::class)->create([
            'file' => $this->setupFileUpload('media', 'image.jpg')
        ]);

        $media->sites()->sync([ $site->getKey() ]);
        $media->publishing_groups()->sync([ $pg->getKey() ]);

        // And now set up our attributes, essentially a duplicate file
        $pg2 = factory(PublishingGroup::class)->create();
        $site2 = factory(Site::class)->create([ 'publishing_group_id' => $pg2->getKey() ]);

        $attrs = [
            'upload' => $this->setupFileUpload('media', 'image.jpg'),

            'site_ids' => [ $site2->getKey() ],
            'publishing_group_ids' => [ $pg2->getKey() ],
        ];

        // Run the request, assert that the count does not increased
        $this->authenticatedAndAuthorized();
        $response = $this->action('POST', MediaController::class . '@store', [], $attrs);

        $media = $media->fresh();
        $this->assertCount(2, $media->sites);
        $this->assertCount(2, $media->publishing_groups);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_ReturnsJson(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals('image.jpg', $json['data']['filename']);
    }

    /**
     * @test
     */
    public function store_WhenAuthorizedAndValid_Returns201(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(201);
    }



    /**
     * @test
     * @group authentication
     */
    public function destroy_WhenUnauthenticated_Returns401(){
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $response = $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], []);
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function delete_WhenAuthorizedAndMediaNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('DELETE', MediaController::class . '@destroy', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
     * @group authorization
     */
    public function destroy_WhenAuthenticatedAndDeletingBySites_ChecksAuthorizationForSites(){
        $sites = factory(Site::class, 2)->states('withPublishingGroup')->create();

        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
        $media->sites()->sync($sites->pluck('id'));

        Gate::shouldReceive('authorize')->with('delete', Mockery::on(function($args) use ($media){
            return (is_array($args)
                && is_a($args[0], Media::class) && ($args[0]->getKey() == $media->getKey())
                && is_a($args[1], Site::class)
            );
        }))->twice();

        $this->authenticated();

        $attrs = [ 'site_ids' => $sites->pluck('id') ];
        $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], $attrs);
    }

    /**
     * @test
     * @group authorization
     */
    public function destroy_WhenAuthenticatedAndDeletingByPublishingGroup_ChecksAuthorizationForPublishingGroup(){
        $pgs = factory(PublishingGroup::class, 2)->create();

        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
        $media->publishing_groups()->sync($pgs->pluck('id'));

        Gate::shouldReceive('authorize')->with('delete', Mockery::on(function($args) use ($media){
            return (is_array($args)
                && is_a($args[0], Media::class) && ($args[0]->getKey() == $media->getKey())
                && is_a($args[1], PublishingGroup::class)
            );
        }))->twice();

        $this->authenticated();

        $attrs = [ 'publishing_group_ids' => $pgs->pluck('id') ];
        $response = $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], $attrs);
    }

    /**
     * @test
     * @group authorization
     */
    public function destroy_WhenAuthenticatedAndUnauthorized_Returns403(){
        $pg = factory(PublishingGroup::class)->create();
        $site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $attrs = [ 'site_ids' => [ $site->getKey() ], 'publishing_group_ids' => [ $pg->getKey() ] ];

        $this->authenticatedAndUnauthorized();

        $response = $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], $attrs);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function destroy_WhenAuthenticatedAndDeletingBySites_UnassociatesSpecifiedSitesOnly(){
        $sites = factory(Site::class, 3)->states('withPublishingGroup')->create();

        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
        $media->sites()->sync($sites->pluck('id'));

        $this->authenticatedAndAuthorized();

        $attrs = [ 'site_ids' => [ $sites[0]->getKey(), $sites[1]->getKey() ]];
        $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], $attrs);

        $media = $media->fresh();
        $this->assertCount(1, $media->sites);
        $this->assertContains($sites[2]->getKey(), $media->sites->pluck('id'));
    }

    /**
     * @test
     */
    public function destroy_WhenAuthenticatedAndDeletingByPublishingGroups_UnassociatesPublishingGroupsOnly(){
        $pgs = factory(PublishingGroup::class, 3)->create();

        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
        $media->publishing_groups()->sync($pgs->pluck('id'));

        $this->authenticatedAndAuthorized();

        $attrs = [ 'publishing_group_ids' => [ $pgs[0]->getKey(), $pgs[1]->getKey() ]];
        $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], $attrs);

        $media = $media->fresh();
        $this->assertCount(1, $media->publishing_groups);
        $this->assertContains($pgs[2]->getKey(), $media->publishing_groups->pluck('id'));
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorizedAndValid_Returns200(){
        $pg = factory(PublishingGroup::class)->create();
        $site = factory(Site::class)->create([ 'publishing_group_id' => $pg->getKey() ]);
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $attrs = [ 'site_ids' => [ $site->getKey() ], 'publishing_group_ids' => [ $pg->getKey() ] ];

        $this->authenticatedAndAuthorized();

        $response = $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], $attrs);
        $response->assertStatus(200);
    }


}
