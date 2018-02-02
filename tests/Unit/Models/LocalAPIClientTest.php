<?php

namespace Tests\Unit\Models;

use App\Models\LocalAPIClient;
use App\Models\Revision;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use App\Models\User;
use App\Models\Site;
use App\Models\Page;
use Tests\TestCase;

class LocalAPIClientTest extends TestCase
{
    public $testTree = [
        [
            'slug' => 'undergraduate',
            'title' => 'Undergraduates',
            'layout' => ['name' => 'test-layout', 'version' => 1],
            'children' => [
                [
                    'slug' => '2017',
                    'title' => '2017 Entry',
                    'layout' => ['name' => 'test-layout', 'version' => 1]
                ],
                [
                    'slug' => '2018',
                    'title' => '2018 Entry',
                    'layout' => ['name' => 'test-layout', 'version' => 1]
                ],
            ]
        ],
        [
            'slug' => 'postgraduate',
            'title' => 'Postgraduates',
            'layout' => ['name' => 'test-layout', 'version' => 1],
            'children' => [
                [
                    'slug' => '2017',
                    'title' => '2017 Entry',
                    'layout' => ['name' => 'test-layout', 'version' => 1]
                ],
                [
                    'slug' => '2018',
                    'title' => '2018 Entry',
                    'layout' => ['name' => 'test-layout', 'version' => 1]
                ],
            ]
        ]
    ];


    public function fixture()
    {
        $user = factory(User::class)->states('admin')->create();
        return new LocalAPIClient($user);
    }

    /**
     * A new site will have a Page with an empty revision.
     * @test
     * @group APICommands
     */
    public function createSite_createsASite()
    {
        $client = $this->fixture();
        $site = $client->createSite(
            'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $this->assertInstanceOf(Site::class, $site);
        $this->assertInstanceOf(Page::class, $site->draftHomepage);
        $this->assertNull($site->draftHomepage->slug);
        $this->assertInstanceOf(Revision::class, $site->draftHomepage->revision);
		$this->assertEquals('Home Page', $site->draftHomepage->revision->title); // as defined in the one-page-site-v1 site template
    }

    /**
     * @test
     * @group APICommands
     */
    public function addPage_addsAPage()
    {
        $client = $this->fixture();
        $site = $client->createSite(
            'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $new_page_title = 'This is a page :)';
        $newpage = $client->addPage(
            $site->draftHomepage->id,
            null,
            'foo',
            [
                'name' => 'test-layout',
                'version' => '1'
            ],
            $new_page_title
        );
        $this->assertInstanceOf(Page::class, $newpage);
        $this->assertInstanceOf(Revision::class, $newpage->revision);
        $this->assertEquals($new_page_title, $newpage->revision->title);
    }

    /**
     * @test
     * @group APICommands
     */
    public function addTree_addsMultiplePages()
    {
        $client = $this->fixture();
        $site = $client->createSite(
            'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $client->addTree($site->draftHomepage->id, null, $this->testTree);
        $expected = [
            "/",
            "/undergraduate",
            "/undergraduate/2017",
            "/undergraduate/2018",
            "/postgraduate",
            "/postgraduate/2017",
            "/postgraduate/2018"
        ];
        $this->assertEquals($expected, Page::where('site_id', $site->id)->orderBy('lft')->pluck('path')->toArray());
    }

    /**
     * @test
     * @group APICommands
     */
    public function addPage_addsAPageAtTheEnd_ifNoBeforeID()
    {
        $client = $this->fixture();
        $site = $client->createSite(
            'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $client->addTree($site->draftHomepage->id, null, $this->testTree);
        $parent = Page::findBySiteAndPath($site->id,'/undergraduate');
        $new_page_title = 'test';
        $newpage = $client->addPage(
            $parent->id,
            null,
            'test',
            ['name' => 'test-layout', 'version' => 1],
            $new_page_title
        );
        $expected = [
            "/",
            "/undergraduate",
            "/undergraduate/2017",
            "/undergraduate/2018",
            "/undergraduate/test",
            "/postgraduate",
            "/postgraduate/2017",
            "/postgraduate/2018"
        ];
        $this->assertEquals($expected, Page::where('site_id', $site->id)->orderBy('lft')->pluck('path')->toArray());
    }

    /**
     * @test
     * @group APICommands
     */
    function addPage_withNoParent_fails()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $client = $this->fixture();
        $site = $client->createSite(
             'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $new_page_title = 'This is a page :)';
        $newpage = $client->addPage(
            null,
            null,
            'foo',
            ['name' => 'test-layout', 'version' => 1],
            $new_page_title
        );
        $this->assertInstanceOf(Validator::class, $newpage);
    }

    /**
     * @test
     * @group APICommands
     */
    function addPage_whenBeforeIsARoot_fails()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $client = $this->fixture();
        $site = $client->createSite(
            'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $new_page_title = 'This is a page :)';
        $newpage = $client->addPage(
            $site->draftHomepage->id,
            null,
            'foo',
            ['name' => 'test-layout', 'version' => 1],
            $new_page_title
        );
        $this->assertInstanceOf(Page::class, $newpage);
        $newpage = $client->addPage(
            $site->draftHomepage->children()->first()->id,
            $site->draftHomepage->id,
            'bar',
            ['name' => 'test-layout', 'version' => 1],
            $new_page_title
        );
    }

    /**
     * @test
     * @group APICommands
     */
    public function addPage_addsAPageBeforeAnotherPage_ifBeforeID()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $client = $this->fixture();
        $site = $client->createSite(
             'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $client->addTree( $site->draftHomepage->id, null, $this->testTree);
        $parent1 = Page::findBySiteAndPath($site->id, '/undergraduate/2018');
        $parent2 = Page::findBySiteAndPath($site->id, '/postgraduate/2017');
        $client->addPage(
            $parent1->parent_id,
            $parent1->id,
            'test',
            ['name' => 'test-layout', 'version' => 1],
            'test1'
        );
        $client->addPage(
            $parent2->parent_id,
            $parent2->id,
            'test',
            'test-layout',
            1,
            'test1'
        );
        $expected = [
            "/",
            "/undergraduate",
            "/undergraduate/2017",
            "/undergraduate/test",
            "/undergraduate/2018",
            "/postgraduate",
            "/postgraduate/test",
            "/postgraduate/2017",
            "/postgraduate/2018",
        ];
        $this->assertEquals($expected, Page::where('site_id', $site->id)->orderBy('lft')->pluck('path')->toArray());
    }

    /**
     * @test
     * @group APICommands
     */
    function updateContent_withNoDraftId_fails()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $client = $this->fixture();
        $site = $client->createSite(
            'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $client->updatePageContent(null, []);
    }

    /**
     * @test
     * @group APICommands
     */
    public function updateContent_withNoBlocks_fails()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $client = $this->fixture();
        $site = $client->createSite(
            'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $client->updatePageContent($site->draftHomepage->id, null);
    }

    /**
     * @test
     * @group APICommands
     */
    public function updateContent_withValidData_works()
    {
        $client = $this->fixture();
        $site = $client->createSite(
            'Test Site', 'example.com', '', ['name' => 'one-page-site', 'version' => 1]
        );
        $homepage = $site->draftHomepage;
        $old_revision = $homepage->revision;

        $valid_data = json_decode(file_get_contents(base_path('tests/Support/Fixtures/api_requests/v1/update_content.json')), true);
        $homepage = $client->updatePageContent($homepage->id, $valid_data['blocks']);
        $this->assertInstanceOf(Page::class, $homepage);
        $this->assertInstanceOf(Revision::class, $site->draftHomepage->revision);
        $new_revision = $homepage->revision;
        $this->assertNotEquals($new_revision->id, $old_revision->id);
    }

}