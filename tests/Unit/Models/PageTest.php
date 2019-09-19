<?php
namespace Tests\Unit\Models;

use Faker;
use Faker\Factory;
use App\Models\Block;
use App\Models\Site;
use App\Models\LocalAPIClient;
use App\Models\User;
use Exception;
use Tests\TestCase;
use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PageTest extends TestCase
{
	public $faker;

	public function setUp(){
		parent::setUp();
		$this->faker = Faker\Factory::create();
	}

	function getTestSite()
	{

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
		$api = new LocalAPIClient($user);
		$site = $api->createSite(
			'test',
			'example.com',
			'',
			['name' => 'valid-one-page-site', 'version' => 1]
		);
		$api->publishPage($site->draftHomepage->id);
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
		$api = new LocalAPIClient($user);
		$site = $api->createSite(
			'test',
			'example.com',
			'',
			['name' => 'valid-one-page-site', 'version' => 1]
		);
		$api->publishPage($site->draftHomepage->id);
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
		$api = new LocalAPIClient($user);
		$site = $api->createSite(
			'test',
			'example.com',
			'',
			['name' => 'valid-one-page-site', 'version' => 1]
		);
		$api->publishPage($site->draftHomepage->id);
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

	/**
	 * @test
	 */
	public function homepageHasNoAncestors()
	{
		$site = factory(Site::class)->create();

		$page = factory(Page::class)->create([
			'site_id' => $site->id,
			'parent_id' => null
		]);

		$ancestorsCount = count($site->draftHomepage->getAncestorsWithRevision());

		$this->assertEquals($ancestorsCount, 0);
	}

	/**
	 * @test
	 */
	public function subpagesHaveExpectedAncestors()
	{
		$site = factory(Site::class)->create();

		$pages[] = factory(Page::class)->create([
			'site_id' => $site->id,
			'parent_id' => null
		]);

		$pages[] = factory(Page::class)->create([
			'site_id' => $site->id,
			'parent_id' => $pages[0]->id,
			'slug' => $this->faker->domainWord
		]);

		$pages[] = factory(Page::class)->create([
			'site_id' => $site->id,
			'parent_id' => $pages[1]->id,
			'slug' => $this->faker->domainWord
		]);

		$pages[] = factory(Page::class)->create([
			'site_id' => $site->id,
			'parent_id' => $pages[2]->id,
			'slug' => $this->faker->domainWord
		]);

		for ($i=0; $i < count($pages); $i++) {
			$page = $pages[$i];
			$ancestors = $page->getAncestorsWithRevision();

			// do we have the correct number of ancestors
			$ancestorsCount = count($ancestors);
			$this->assertEquals($ancestorsCount, $i);

			// do we have the correct parent ancestor
			if ($i) {
				$this->assertEquals($page->parent_id, $ancestors[$i-1]->id);
			}
		}
	}

	/**
	 * @test
	 * @group panic
	 */
	public function subpagesOfChildSitesHaveExpectedAncestors()
	{
		// GIVEN we have a parent site with pages and a subsite with paths at the deepest level
		$parentSite = factory(Site::class)->create();

		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'parent_id' => null
		]);

		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'slug' => $this->faker->domainWord,
			'parent_id' => $pages[0]->id
		]);

		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'slug' => $this->faker->domainWord,
			'parent_id' => $pages[1]->id
		]);

		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'slug' => $this->faker->domainWord,
			'parent_id' => $pages[2]->id
		]);

		$parentSiteAncestorCount = count($pages[count($pages)-1]->getAncestorsWithRevision());

		$childSite = factory(Site::class)->create([
			'host' => $parentSite->host,
			'path' => $parentSite->path . $pages[count($pages)-1]->path .  $this->faker->domainWord
		]);

		$childSitePages[] = factory(Page::class)->create([
			'site_id' => $childSite->id,
			'parent_id' => null
		]);

		$childSitePages[] = factory(Page::class)->create([
			'site_id' => $childSite->id,
			'slug' => $this->faker->domainWord,
			'parent_id' => $childSitePages[0]->id
		]);

		$childSitePages[] = factory(Page::class)->create([
			'site_id' => $childSite->id,
			'slug' => $this->faker->domainWord,
			'parent_id' => $childSitePages[1]->id
		]);

		$childSitePages[] = factory(Page::class)->create([
			'site_id' => $childSite->id,
			'slug' => $this->faker->domainWord,
			'parent_id' => $childSitePages[2]->id
		]);

		// WHEN we count ancestors of each page in the parent
		for ($i=0; $i < count($pages); $i++) {
			$page = $pages[$i];
			$ancestors = $page->getAncestorsWithRevision();

			// do we have the correct number of ancestors
			$ancestorsCount = count($ancestors);
			$this->assertEquals($ancestorsCount, $i);

			// do we have the correct parent ancestor
			if ($i) {
				$this->assertEquals($page->parent_id, $ancestors[$i-1]->id);
			}
		}

		// WHEN we count ancestors of each page in the child site
		for ($i=0; $i < count($childSitePages); $i++) {
			$page = $childSitePages[$i];
			$ancestors = $page->getAncestorsWithRevision();
			$ancestorsCount = count($ancestors);

			// THEN the count should be correct
			$this->assertEquals($ancestorsCount, $i + $parentSiteAncestorCount);

			// AND if the page has a parent id then it should match the expected ancestor id
			if ($i) {
				$this->assertEquals($page->parent_id, $ancestors[$ancestorsCount-1]->id);
			} else {
				// if this the homepage then it has no parent id so we need to check that the ancestor's full_path
				// is the start of the homepage's full path
                $parent_path = $ancestors[$ancestorsCount-1]->full_path;
                $this->assertEquals($parent_path, substr($page->full_path, 0, strlen($parent_path)));
			}
		}
	}

	/**
	 * @test
	 */
	public function homepageOfImmediateChildSiteOnlyHasParentHomepageAsAncestor()
	{
		$parentSite = factory(Site::class)->create();

		$parentSiteHomePage = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'parent_id' => null
		]);

		$childSite = factory(Site::class)->create([
			'host' => $parentSite->host,
			'path' => $parentSite->path . $this->faker->domainWord
		]);

		$childSiteHomePage = factory(Page::class)->create([
			'site_id' => $childSite->id,
			'parent_id' => null
		]);

		$ancestorsCount = count($childSite->draftHomepage->getAncestorsWithRevision());

		$this->assertEquals($ancestorsCount, 1);
	}

	/**
	 * @test
	 */
	public function homepageOfChildSiteDeepWithinParentHasManyAncestors()
	{
		$parentSite = factory(Site::class)->create();

		$pages = [];

		// parent site homepage
		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'parent_id' => null
		]);

		// parent site level 1 page
		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'parent_id' => $pages[0]->id,
			'slug' => $this->faker->domainWord
		]);

		// parent site level 2 page
		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'parent_id' => $pages[1]->id,
			'slug' => $this->faker->domainWord
		]);

		$childSite = factory(Site::class)->create([
			'host' => $parentSite->host,
			'path' => $parentSite->path . $pages[2]->path . '/' . $this->faker->domainWord
		]);

		$childSiteHomePage = factory(Page::class)->create([
			'site_id' => $childSite->id,
			'parent_id' => null
		]);

		$ancestorsCount = count($childSite->draftHomepage->getAncestorsWithRevision());

		$this->assertEquals($ancestorsCount, count($pages));
	}

	/**
	 * @test
	 */
	public function homepageOfChildSiteDeepWithinNoneTopLevelParentHasManyAncestors()
	{
		$parentSite = factory(Site::class)->create([
			'path' => '/this/is/a/deep/parent'
		]);

		$pages = [];

		// parent site homepage
		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'parent_id' => null
		]);

		// parent site level 1 page
		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'parent_id' => $pages[0]->id,
			'slug' => $this->faker->domainWord
		]);

		// parent site level 2 page
		$pages[] = factory(Page::class)->create([
			'site_id' => $parentSite->id,
			'parent_id' => $pages[1]->id,
			'slug' => $this->faker->domainWord
		]);

		$childSite = factory(Site::class)->create([
			'host' => $parentSite->host,
			'path' => $parentSite->path . $pages[2]->path . '/' . $this->faker->domainWord
		]);

		$childSiteHomePage = factory(Page::class)->create([
			'site_id' => $childSite->id,
			'parent_id' => null
		]);

		$ancestorsCount = count($childSite->draftHomepage->getAncestorsWithRevision());

		$this->assertEquals(count($pages), $ancestorsCount);
	}
}
