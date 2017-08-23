<?php

namespace Tests\Unit\Models;

use App\Models\LocalAPIClient;
use App\Models\PublishingGroup;
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
     */
    public function createSite_createsASite()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
        );
        $this->assertInstanceOf(Site::class, $site);
        $this->assertInstanceOf(Page::class, $site->homepage);
        $this->assertInstanceOf(Revision::class, $site->homepage->revision);
    }

    /**
     * @test
     */
    public function addPage_addsAPage()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
        );
        $new_page_title = 'This is a page :)';
        $newpage = $client->addPage(
            $site->homepage->id,
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
     */
    public function addTree_addsMultiplePages()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
        );
        $client->addTree($site->homepage->id, null, $this->testTree);
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
     */
    public function addPage_addsAPageAtTheEnd_ifNoBeforeID()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
        );
        $client->addTree($site->homepage->id, null, $this->testTree);
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
     */
    function addPage_withNoParent_fails()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
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
     */
    function addPage_whenBeforeIsARoot_fails()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
        );
        $new_page_title = 'This is a page :)';
        $newpage = $client->addPage(
            $site->homepage->id,
            null,
            'foo',
            ['name' => 'test-layout', 'version' => 1],
            $new_page_title
        );
        $this->assertInstanceOf(Page::class, $newpage);
        $newpage = $client->addPage(
            $site->homepage->children()->first()->id,
            $site->homepage->id,
            'bar',
            ['name' => 'test-layout', 'version' => 1],
            $new_page_title
        );
    }

    /**
     * @test
     */
    public function addPage_addsAPageBeforeAnotherPage_ifBeforeID()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
        );
        $client->addTree( $site->homepage->id, null, $this->testTree);
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
     */
    function updateContent_withNoDraftId_fails()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
        );
        $client->updatePageContent(null, []);
    }

    /**
     * @test
     */
    public function updateContent_withNoBlocks_fails()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
        );
        $client->updatePageContent($site->homepage->id, null);
    }

    /**
     * @test
     */
    public function updateContent_withValidData_works()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', ['name' => 'test-layout', 'version' => 1]
        );
        $homepage = $site->homepage;
        $old_revision = $homepage->revision;
        $homepage = $client->updatePageContent($homepage->id, ['main' => []]);
        $this->assertInstanceOf(Page::class, $homepage);
        $this->assertInstanceOf(Revision::class, $site->homepage->revision);
        $new_revision = $homepage->revision;
        $this->assertNotEquals($new_revision->id, $old_revision->id);
    }

}