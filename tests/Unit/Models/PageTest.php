<?php
namespace Tests\Unit\Models;

use App\Models\Block;
use App\Models\LocalAPIClient;
use App\Models\PublishingGroup;
use App\Models\Scopes\VersionScope;
use App\Models\User;
use Exception;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Redirect;
use App\Http\Transformers\Api\v1\PageTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PageTest extends TestCase
{
    function getTestSite()
    {

    }

    /**
     * @test
     */
    function draftVersionScope_isSetByDefaultWhenPageIsBooted()
    {
        $page = new Page();
        $scope = Page::getGlobalScope(VersionScope::class);
        $this->assertInstanceOf(VersionScope::class, $scope);
        $this->assertEquals(Page::STATE_DRAFT, $scope->version);
    }

    /**
     * @test
     */
    function pageHierarchyIsScoped_bySiteAndVersion()
    {
        $page = new Page();
        $cols = $page->getScopedColumns();
        sort($cols);
        $this->assertEquals(['site_id', 'version'], $cols);
    }

	/**
	 * @test
	 */
	function scopePublished_ReturnsPublishedPagesOnly()
	{
	    $user = factory(User::class)->create();
	    $pubgroup = factory(PublishingGroup::class)->create();
	    $pubgroup->users()->attach($user);
        $api = new LocalAPIClient($user);
        $site = $api->createSite(
            $pubgroup->id,
            'test',
            'example.com',
            '',
            ['name' => 'test-layout', 'version' => 1]
        );
        $api->publishPage($site->homepage->id);
	    $pages = Page::published()->get();
	    $this->assertCount(1, $pages);
	    foreach($pages as $page){
	        $this->assertEquals(Page::STATE_PUBLISHED, $page->version);
        }
	}

	/**
	 * @test
	 */
	function scopeDrafts_ReturnsDraftPagesOnly()
	{
        $user = factory(User::class)->create();
        $pubgroup = factory(PublishingGroup::class)->create();
        $pubgroup->users()->attach($user);
        $api = new LocalAPIClient($user);
        $site = $api->createSite(
            $pubgroup->id,
            'test',
            'example.com',
            '',
            ['name' => 'test-layout', 'version' => 1]
        );
        $api->publishPage($site->homepage->id);
        $pages = Page::draft()->get();
        $this->assertCount(1, $pages);
        foreach($pages as $page){
            $this->assertEquals(Page::STATE_DRAFT, $page->version);
        }
	}

    /**
     * @test
     */
    function scopeVersion_restrictsToSpecifiedVersion()
    {
        $user = factory(User::class)->create();
        $pubgroup = factory(PublishingGroup::class)->create();
        $pubgroup->users()->attach($user);
        $api = new LocalAPIClient($user);
        $site = $api->createSite(
            $pubgroup->id,
            'test',
            'example.com',
            '',
            ['name' => 'test-layout', 'version' => 1]
        );
        $api->publishPage($site->homepage->id);
        $pages = Page::version(Page::STATE_DRAFT)->get();
        $this->assertCount(1, $pages);
        $this->assertEquals(Page::STATE_DRAFT, $pages[0]->version);
        $pages = Page::version(Page::STATE_PUBLISHED)->get();
        $this->assertCount(1, $pages);
        $this->assertEquals(Page::STATE_PUBLISHED, $pages[0]->version);
    }

    /**
     * @test
     */
    public function scopeAny_removesVersionScope()
    {
        $user = factory(User::class)->create();
        $pubgroup = factory(PublishingGroup::class)->create();
        $pubgroup->users()->attach($user);
        $api = new LocalAPIClient($user);
        $site = $api->createSite(
            $pubgroup->id,
            'test',
            'example.com',
            '',
            ['name' => 'test-layout', 'version' => 1]
        );
        $api->publishPage($site->homepage->id);
        $pages = Page::anyVersion()->orderBy('version')->get();

        $this->assertCount(2, $pages);
        $this->assertEquals(Page::STATE_DRAFT, $pages[0]->version);
        $this->assertEquals(Page::STATE_PUBLISHED, $pages[1]->version);
    }

    /**
     * @test
     */
    public function scopeForSite_restrictsToSpecifiedSite()
    {
        return $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function scopeForSiteAndPath_restrictsToSpecificSiteAndPath()
    {
        return $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function publishedVersion_retrievesPublishedPageMatchingPagesPath()
    {
        return $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function publishedVersion_returnsFalseIfNoPublishedVersion()
    {
        return $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function findBySiteAndPath_returnsPageIfExists()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function findBySiteAndPath_returnsNullIfSiteOrPageDoesNotExist()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function findByHostAndPath_returnsPageIfExists()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function findByHostAndPath_returnsNullIfSiteOrPageDoesNotExist()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function findByHostAndPath_takesVersionIntoAccount()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function isPublishedVersion_returnsTrueIfPageIsPublishedVersion()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function isPublishedVersion_returnsFalseIfPageIsNotPublishedVersion()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    function bake_includesCorrectData()
    {
        return $this->markTestIncomplete();
    }

    /**
     * @test
     */
    function bake_returnsEmptyArrayIfNoRegionsExist()
    {
        return $this->markTestIncomplete();
    }

    /**
     * @test
     */
    function bake_includesMediaWherePresent()
    {
        return $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function setRevision_setsRevisionAsPublished_ifPageIsPublished()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function setRevision_doesNotSetRevisionAsPublished_ifPageIsNotPublished()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function isPublishedVersion_returnsTrue_ifPageIsPublishedVersion()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function isPublishedVersion_returnsFalse_ifPageIsNotPublishedVersion()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function setRevision_setsRevision()
    {
        $this->markTestIncomplete();
    }

    /**
	 * @test
	 */
	function generatePath_WhenNoParentOrSlugIsSet_SetsPathToRoot()
	{
		$route = factory(Page::class)->states('withRevision')->make([ 'slug' => null ]);
		$path = $route->generatePath();
		$this->assertEquals('/', $path);
	}

	/**
	 * @test
	 */
	function generatePath_WhenNoParentButSlugIsSet_ThrowsException()
	{
		$route = factory(Page::class)->states('withRevision')->make(['slug' => 'foo']);
		$this->expectException(Exception::class);
		$route->generatePath();
	}

	/**
	 * @test
	 */
	function generatePath_WhenHasParents_SetsPathUsingParentSlugs()
	{
		$r1 = factory(Page::class)->states('withRevision')->create();
		$r2 = factory(Page::class)->states('withRevision')->create([ 'slug' => 'foo', 'parent_id' => $r1->getKey() , 'site_id' => $r1->site_id]);
		$r3 = factory(Page::class)->states('withRevision')->make([ 'slug' => 'bar', 'parent_id' => $r2->getKey() , 'site_id' => $r1->site_id]);
		$path = $r3->generatePath();
		$this->assertEquals('/' . $r2->slug . '/' . $r3->slug, $path); // $r1 is a root node, so has no slug
	}



	/**
	 * @test
	 */
	function whenSaving_GeneratesPath()
	{
		$r1 = factory(Page::class)->states( 'withRevision')->create();
		$r2 = factory(Page::class)->states('withRevision')->create([ 'slug' => 'foo', 'parent_id' => $r1->getKey(), 'site_id' => $r1->site_id ]);
		$r3 = factory(Page::class)->states('withRevision')->make([ 'slug' => 'bar', 'parent_id' => $r2->getKey(), 'site_id' => $r1->site_id ]);
		$r3->save();
		$this->assertEquals('/' . $r2->slug . '/' . $r3->slug, $r3->path); // $r1 is a root node, so has no slug
	}



	/**
	 * @test
	 */
	public function findByPath_WhenPathExists_ReturnsItem()
	{
        return $this->markTestIncomplete();
		$route = factory(Page::class)->states('withParent', 'withRevision')->create();
		$result = Page::findByPath($route->path);
		$this->assertEquals($route->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findBySiteAndPath_WhenPathDoesNotExist_ReturnsNull()
	{
	    return $this->markTestIncomplete();
		$this->assertNull(Page::findBySiteAndPath(999,'/foobar'));
	}



	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathExists_ReturnsItem()
	{
        return $this->markTestIncomplete();
		$route = factory(Page::class)->states('withParent', 'withRevision')->create();

		$result = Page::findByPathOrFail($route->path);
		$this->assertEquals($route->getKey(), $result->getKey());
	}

	/**
	 * @test
	 */
	public function findByPathOrFail_WhenPathDoesNotExist_ReturnsNull()
	{
        return $this->markTestIncomplete();
        $this->expectException(ModelNotFoundException::class);
		Page::findByPathOrFail('/foobar');
	}

    /**
     * @test
     * @group ignore
     */
    public function clearRegion_DeletesAllBlocksForGivenPageAndRegion()
    {
        $page = factory(Page::class)->create();
        factory(Block::class, 3)->create([ 'page_id' => $page->getKey() ]);

        $page->clearRegion('test-region');
        $this->assertEquals(0, $page->blocks()->count());
    }

    /**
     * @test
     * @group ignore
     */
    public function clearRegion_DoesNotDeleteBlocksInOtherRegions()
    {
        $page = factory(Page::class)->create();

        factory(Block::class, 3)->create([ 'page_id' => $page->getKey() ]);
        factory(Block::class, 2)->create([ 'page_id' => $page->getKey(), 'region_name' => 'foobar' ]);

        $page->clearRegion('foobar');
        $this->assertEquals(3, $page->blocks()->count());
    }



    /**
     * @test
     * @group ignore
     */
    public function getPageDefinition_ReturnLayoutDefinition(){
        return $this->markTestIncomplete();
        $page = factory(Page::class)->make();
        $this->assertInstanceOf(LayoutDefinition::class, $page->getLayoutDefinition());
    }



    /**
     * @test
     * @group ignore
     */
    public function getLayoutDefinition_WhenPageDefinitionIsNotLoaded_LoadsSupportedLayoutDefinition(){
        return $this->markTestIncomplete();
        $page = factory(Page::class)->make();
        $definition = $page->getLayoutDefinition();

        $this->assertNotEmpty($definition);
        $this->assertEquals('test-layout', $definition->name);
    }

    /**
     * @test
     * @group ignore
     */
    public function getLayoutDefinition_WhenLayoutDefinitionIsLoaded_DoesNotReloadLayoutDefinition(){
        return $this->markTestIncomplete();
        $page = factory(Page::class)->make();
        $page->getLayoutDefinition(); 					// This should populate $pageDefinition

        $page = Mockery::mock($page)->makePartial()->shouldAllowMockingProtectedMethods();
        $page->shouldNotReceive('loadLayoutDefinition');

        $definition = $page->getLayoutDefinition(); 	// This should not re-populate $pageDefinition
        $this->assertNotEmpty($definition);				// Is populated, but not empty.
    }

    /**
     * @test
     * @group ignore
     */
    public function getLayoutDefinition_WithRegionDefinitionsWhenLayoutDefinitionIsLoadedWithoutRegions_HasRegionDefinitions()
    {
        return $this->markTestIncomplete();
        $page = factory(Page::class)->make();
        $page->loadLayoutDefinition();

        $definition = $page->getLayoutDefinition(true);

        // Ensure that our assertion does not trigger loading of Region definitions
        $definition = Mockery::mock($definition)->makePartial()->shouldAllowMockingProtectedMethods();
        $definition->shouldNotReceive('loadRegionDefinitions');

        $this->assertCount(1, $definition->getRegionDefinitions());
    }

    /**
     * @test
     * @group ignore
     */
    public function getLayoutDefinition_WithRegionDefinitionsWhenLayoutDefinitionIsLoadedWithRegions_HasRegionDefinitions()
    {
        return $this->markTestIncomplete();
        $page = factory(Page::class)->make();
        $definition = $page->getLayoutDefinition(true);

        // Ensure that our assertion does not trigger loading of Region definitions
        $definition = Mockery::mock($definition)->makePartial()->shouldAllowMockingProtectedMethods();
        $definition->shouldNotReceive('loadRegionDefinitions');

        $this->assertCount(1, $definition->getRegionDefinitions());
    }



}
