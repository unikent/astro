<?php
namespace Tests\Unit\Http\Controllers\Api\v1;

use Gate;
use Mockery;
use App\Models\Site;
use App\Models\Media;
use Tests\FileUploadTrait;
use Tests\FileCleanupTrait;
use Illuminate\Support\Collection;
use App\Http\Controllers\Api\v1\MediaController;
use Illuminate\Auth\Access\AuthorizationException;

class MediaControllerTest extends ApiControllerTestCase {

    use FileUploadTrait, FileCleanupTrait;


    public function getAttrs( $pg = null, Site $site = null)
    {
        $site = $site ?: factory(Site::class)->create();

        return [
            'upload' => $this->setupFileUpload('media', 'image.jpg'),
            'site_ids' => [ $site->getKey() ],
        ];
    }



    /**
     * @test
	 * @group media
     * @group authentication
     */
    public function index_WhenUnauthenticated_Returns401(){
        $response = $this->action('GET', MediaController::class . '@index');
        $response->assertStatus(401);
    }

    /**
     * @test
	 * @group media
     * @group authentication
     */
    public function index_WhenAuthenticatedAndRequestIsWithoutSiteIdsAndPublishingGroupIds_ChecksAuthorization(){
    	return $this->markTestIncomplete();
        Gate::shouldReceive('authorize')->with('index', Media::class)->once();

        $this->authenticated();
        $response = $this->action('GET', MediaController::class . '@index');
    }

    /**
     * @test
	 * @group media
     * @group authentication
     */
    public function index_WhenAuthenticatedAndRequestHasSiteIds_ChecksAuthorization(){
        $sites = factory(Site::class, 3)->create();

        $media = new Collection([
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
        ]);

        $media[0]->sites()->attach($sites[0]);
        $media[1]->sites()->attach($sites[1]);
        $media[2]->sites()->attach($sites[2]);

        Gate::shouldReceive('authorize')->with('index', Media::class)->times(0);

        Gate::shouldReceive('authorize')->with('index', Mockery::on(function($args){
            return (is_array($args) && ($args[0] == Media::class) && is_a($args[1], Site::class));
        }))->times(2);

        $this->authenticated();

        $response = $this->action('GET', MediaController::class . '@index', [
            'site_ids' => [ $sites[0]->getKey(), $sites[1]->getKey() ]
        ]);
    }

    /**
     * @test
	 * @group media
     * @group authentication
     */
    public function index_WhenAuthenticatedAndRequestHasPublishingGroupIds_ChecksAuthorization(){
		return $this->markTestIncomplete();

        $media = new Collection([
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
        ]);

        $media[0]->publishing_groups()->attach($pgs[0]);
        $media[1]->publishing_groups()->attach($pgs[1]);
        $media[2]->publishing_groups()->attach($pgs[2]);

        Gate::shouldReceive('authorize')->with('index', Media::class)->times(0);

        Gate::shouldReceive('authorize')->with('index', Mockery::on(function($args){
            return (is_array($args) && ($args[0] == Media::class) && is_a($args[1], PublishingGroup::class));
        }))->times(2);

        $this->authenticated();

        $response = $this->action('GET', MediaController::class . '@index', [
            'publishing_group_ids' => [ $pgs[0]->getKey(), $pgs[1]->getKey() ]
        ]);
    }

    /**
     * @test
	 * @group media
     * @group authentication
     */
    public function index_WhenAuthenticatedAndRequestIsWithoutSiteIdsAndPublishingGroupIdsAndUnauthorized_Returns403(){
        $this->authenticated();

        Gate::shouldReceive('authorize')->with('index', Media::class)->andThrow(AuthorizationException::class);

        $response = $this->action('GET', MediaController::class . '@index');
        $response->assertStatus(403);
    }

    /**
     * @test
	 * @group media
     * @group authentication
     */
    public function index_WhenAuthenticatedAndRequestHasSiteIdsAndUnauthorized_Returns403(){
        $sites = factory(Site::class, 3)->create();

        $media = new Collection([
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
        ]);

        $media[0]->sites()->attach($sites[0]);
        $media[1]->sites()->attach($sites[1]);
        $media[2]->sites()->attach($sites[2]);

        $this->authenticated();

        Gate::shouldReceive('authorize')->with('index', Mockery::on(function($args){
            return (is_array($args) && ($args[0] == Media::class) && is_a($args[1], Site::class));
        }))->andThrow(AuthorizationException::class);

        $response = $this->action('GET', MediaController::class . '@index', [
            'site_ids' => [ $sites[0]->getKey(), $sites[1]->getKey() ]
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
	 * @group media
     * @group authentication
     */
    public function index_WhenAuthenticatedAndRequestHasPublishingGroupIdsAndUnauthorized_Returns403(){
		return $this->markTestIncomplete();

        $media = new Collection([
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
        ]);

        $media[0]->publishing_groups()->attach($pgs[0]);
        $media[1]->publishing_groups()->attach($pgs[1]);
        $media[2]->publishing_groups()->attach($pgs[2]);

        $this->authenticated();

        Gate::shouldReceive('authorize')->with('index', Mockery::on(function($args){
            return (is_array($args) && ($args[0] == Media::class) && is_a($args[1], PublishingGroup::class));
        }))->andThrow(AuthorizationException::class);

        $response = $this->action('GET', MediaController::class . '@index', [
            'publishing_group_ids' => [ $pgs[0]->getKey(), $pgs[1]->getKey() ]
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
	 * @group media
     */
    public function index_WhenAuthorizedAndRequestHasSiteIds_ReturnsJsonOfMediaAssociatedWithSiteIds(){
        $sites = factory(Site::class, 3)->create();

        $media = new Collection([
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
        ]);

        $media[0]->sites()->attach($sites[0]);
        $media[1]->sites()->attach($sites[1]);
        $media[2]->sites()->attach($sites[2]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', MediaController::class . '@index', [
            'site_ids' => [ $sites[0]->getKey(), $sites[1]->getKey() ]
        ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);

        $this->assertCount(2, $json['data']);
        $this->assertEquals($media[0]->getKey(), $json['data'][0]['id']);
        $this->assertEquals($media[1]->getKey(), $json['data'][1]['id']);
    }

    /**
     * @test
	 * @group media
     */
    public function index_WhenAuthorizedRequestHasPublishingGroupIds_ReturnsJsonOfMediaAssociatedWithPublishingGroups(){
		return $this->markTestIncomplete();

        $media = new Collection([
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]),
        ]);

        $media[0]->publishing_groups()->attach($pgs[0]);
        $media[1]->publishing_groups()->attach($pgs[1]);
        $media[2]->publishing_groups()->attach($pgs[2]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', MediaController::class . '@index', [
            'publishing_group_ids' => [ $pgs[0]->getKey(), $pgs[1]->getKey() ]
        ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);

        $this->assertCount(2, $json['data']);
        $this->assertEquals($media[0]->getKey(), $json['data'][0]['id']);
        $this->assertEquals($media[1]->getKey(), $json['data'][1]['id']);
    }

    /**
     * @test
	 * @group media
     */
    public function index_WhenAuthorizedAndRequestHasTypes_ReturnsJsonOfMedia(){
        $media = new Collection([
            factory(Media::class)->create([ 'type' => 'image', 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'type' => 'document', 'file' => $this->setupFile('media', 'document.pdf') ]),
            factory(Media::class)->create([ 'type' => 'audio', 'file' => $this->setupFile('media', 'audio.mp3') ]),
        ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', MediaController::class . '@index');

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertCount(3, $json['data']);
    }

    /**
     * @test
	 * @group media
     */
    public function index_WhenAuthorizedAndRequestHasTypes_ReturnsJsonOfMediaFilteredByTypes(){
        $media = new Collection([
            factory(Media::class)->create([ 'type' => 'image', 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'type' => 'document', 'file' => $this->setupFile('media', 'document.pdf') ]),
            factory(Media::class)->create([ 'type' => 'audio', 'file' => $this->setupFile('media', 'audio.mp3') ]),
        ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', MediaController::class . '@index', [
            'types' => [ 'image', 'audio' ]
        ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);

        $this->assertCount(2, $json['data']);
        $this->assertEquals($media[0]->getKey(), $json['data'][0]['id']);
        $this->assertEquals($media[2]->getKey(), $json['data'][1]['id']);
    }

    /**
     * @test
	 * @group media
     */
    public function index_WhenAuthorizedAndRequestHasMimeTypes_ReturnsJsonOfMediaFilteredByMimeTypes(){
        $media = new Collection([
            factory(Media::class)->create([ 'type' => 'image', 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'type' => 'document', 'file' => $this->setupFile('media', 'document.pdf') ]),
            factory(Media::class)->create([ 'type' => 'audio', 'file' => $this->setupFile('media', 'audio.mp3') ]),
        ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', MediaController::class . '@index', [
            'mime_types' => [ 'image/jpeg', 'audio/mpeg' ]
        ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);

        $this->assertCount(2, $json['data']);
        $this->assertEquals($media[0]->getKey(), $json['data'][0]['id']);
        $this->assertEquals($media[2]->getKey(), $json['data'][1]['id']);
    }

    /**
     * @test
	 * @group media
     */
    public function index_WhenAuthorized_Returns200(){
        $media = new Collection([
            factory(Media::class)->create([ 'type' => 'image', 'file' => $this->setupFile('media', 'image.jpg') ]),
            factory(Media::class)->create([ 'type' => 'document', 'file' => $this->setupFile('media', 'document.pdf') ]),
            factory(Media::class)->create([ 'type' => 'audio', 'file' => $this->setupFile('media', 'audio.mp3') ]),
        ]);

        $this->authenticatedAndAuthorized();

        $response = $this->action('GET', MediaController::class . '@index');
        $response->assertStatus(200);
    }



    /**
     * @test
	 * @group media
     * @group authentication
     */
    public function store_WhenUnauthenticated_Returns401(){
        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(401);
    }

    /**
     * @test
	 * @group media
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
	 * @group media
     * @group authorization
     */
    public function store_WhenAuthenticated_ChecksAuthorizationForPublishingGroup(){
		return $this->markTestIncomplete();
        Gate::shouldReceive('authorize')->with('create', Mockery::on(function($args){
            return (is_array($args) && is_a($args[0], Media::class) && is_a($args[1], PublishingGroup::class));
        }))->once();

        Gate::shouldReceive('authorize')->with('create', Mockery::any())->andReturn(true);

        $this->authenticated();
        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function store_WhenAuthenticatedAndUnauthorized_Returns403(){
        $this->authenticatedAndUnauthorized();

        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(403);
    }

    /**
     * @test
	 * @group media
     */
    public function store_WhenAuthorizedAndValidAndMediaDoesNotExist_CreatesMedia(){
        $count = Media::count();

        $this->authenticatedAndAuthorized();
        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());

        $this->assertEquals($count+1, Media::count());
    }

    /**
     * @test
	 * @group media
     */
    public function store_WhenAuthorizedAndValidAndMediaDoesNotExist_AssociatesMediaWithSiteAndPublishingGroup(){
		return $this->markTestIncomplete();
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
	 * @group media
     */
    public function store_WhenAuthorizedAndValidAndMediaAlreadyExists_DoesNotCreateNewMedia(){
		return $this->markTestIncomplete();
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
	 * @group media
     */
    public function store_WhenAuthorizedAndValidAndMediaAlreadyExists_AssociatesExisitngMediaWithSiteAndPublishingGroup(){
		return $this->markTestIncomplete();
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
	 * @group media
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
	 * @group media
     */
    public function store_WhenAuthorizedAndValid_Returns201(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('POST', MediaController::class . '@store', [], $this->getAttrs());
        $response->assertStatus(201);
    }



    /**
     * @test
	 * @group media
     * @group authentication
     */
    public function destroy_WhenUnauthenticated_Returns401(){
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $response = $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], []);
        $response->assertStatus(401);
    }

    /**
     * @test
	 * @group media
     */
    public function delete_WhenAuthorizedAndMediaNotFound_Returns404(){
        $this->authenticatedAndAuthorized();

        $response = $this->action('DELETE', MediaController::class . '@destroy', [ 123 ]);
        $response->assertStatus(404);
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function destroy_WhenAuthenticatedAndDeletingBySites_ChecksAuthorizationForSites(){
        $sites = factory(Site::class, 2)->create();

        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);
        $media->sites()->sync($sites->pluck('id'));

        Gate::shouldReceive('authorize')->with('delete', Mockery::on(function($args) use ($media){
            return (is_array($args)
                && is_a($args[0], Media::class) && ($args[0]->getKey() == $media->getKey())
                && is_a($args[1], Site::class)
            );
        }))->twice();

        $this->authenticated();

        $attrs = [ 'site_ids' => $sites->pluck('id')->toArray() ];
        $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], $attrs);
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function destroy_WhenAuthenticatedAndDeletingByPublishingGroup_ChecksAuthorizationForPublishingGroup(){
		return $this->markTestIncomplete();
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

        $attrs = [ 'publishing_group_ids' => $pgs->pluck('id')->toArray() ];
        $response = $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], $attrs);
    }

    /**
     * @test
	 * @group media
     * @group authorization
     */
    public function destroy_WhenAuthenticatedAndUnauthorized_Returns403(){
		return $this->markTestIncomplete();
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
	 * @group media
     */
    public function destroy_WhenAuthenticatedAndDeletingBySites_UnassociatesSpecifiedSitesOnly(){
        $sites = factory(Site::class, 3)->create();

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
	 * @group media
     */
    public function destroy_WhenAuthenticatedAndDeletingByPublishingGroups_UnassociatesPublishingGroupsOnly(){
		return $this->markTestIncomplete();
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
	 * @group media
     */
    public function destroy_WhenAuthorizedAndValid_Returns200(){
		return $this->markTestIncomplete();
        $site = factory(Site::class)->create();
        $media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

        $attrs = [ 'site_ids' => [ $site->getKey() ],];

        $this->authenticatedAndAuthorized();

        $response = $this->action('DELETE', MediaController::class . '@destroy', [ $media->getKey() ], $attrs);
        $response->assertStatus(200);
    }


}
