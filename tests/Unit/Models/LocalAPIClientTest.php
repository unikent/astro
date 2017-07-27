<?php

namespace Tests\Unit\Models;

use App\Models\LocalAPIClient;
use App\Models\PublishingGroup;
use Illuminate\Contracts\Validation\Validator;
use Mockery;
use App\Models\User;
use App\Models\Site;
use App\Models\Page;
use App\Models\PageContent;
use Tests\TestCase;

class LocalAPIClientTest extends TestCase
{
    public $testTree = [
        [
            'slug' => 'undergraduate',
            'title' => 'Undergraduates',
            'layout_name' => 'test-layout',
            'layout_version' => 1,
            'children' => [
                [
                    'slug' => '2017',
                    'title' => '2017 Entry',
                    'layout_name' => 'test-layout',
                    'layout_version' => 1
                ],
                [
                    'slug' => '2018',
                    'title' => '2018 Entry',
                    'layout_name' => 'test-layout',
                    'layout_version' => 1
                ],
            ]
        ],
        [
            'slug' => 'postgraduate',
            'title' => 'Postgraduates',
            'layout_name' => 'test-layout',
            'layout_version' => 1,
            'children' => [
                [
                    'slug' => '2017',
                    'title' => '2017 Entry',
                    'layout_name' => 'test-layout',
                    'layout_version' => 1
                ],
                [
                    'slug' => '2018',
                    'title' => '2018 Entry',
                    'layout_name' => 'test-layout',
                    'layout_version' => 1
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
     * A new site will have a Page with some draft PageContent.
     * @test
     */
    public function createSite_createsASite()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', 'test-layout', 1
        );
        $this->assertInstanceOf(Site::class, $site);
        $this->assertInstanceOf(Page::class, $site->pages()->first());
        $this->assertInstanceOf(PageContent::class, $site->pages()->first()->draft);
    }

    /**
     * @test
     */
    public function addPage_addsAPage()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', 'test-layout', 1
        );
        $new_page_title = 'This is a page :)';
        $newpage = $client->addPage(
            $site->id,
            $site->homePage->id,
            null,
            'foo',
            'test-layout',
            1,
            $new_page_title
        );
        $this->assertInstanceOf(Page::class, $newpage);
        $draft = $newpage->draft;
        $this->assertInstanceOf(PageContent::class, $draft);
        $this->assertEquals($new_page_title, $draft->title);
    }

    /**
     * @test
     */
    public function addTree_addsMultiplePages()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', 'test-layout', 1
        );
        $client->addTree($site->id, $site->homePage->id, null, $this->testTree);
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
            $publishing_group_id, 'Test Site', 'example.com', '', 'test-layout', 1
        );
        $client->addTree($site->id, $site->homePage->id, null, $this->testTree);
        $parent = Page::findByPath('/undergraduate');
        $new_page_title = 'test';
        $newpage = $client->addPage(
            $site->id,
            $parent->id,
            null,
            'test',
            'test-layout',
            1,
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
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', 'test-layout', 1
        );
        $new_page_title = 'This is a page :)';
        $newpage = $client->addPage(
            $site->id,
            null,
            null,
            'foo',
            'test-layout',
            1,
            $new_page_title
        );
        $this->assertInstanceOf(Validator::class, $newpage);
    }

    /**
     * @test
     */
    function addPage_whenBeforeIsARoot_fails()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', 'test-layout', 1
        );
        $new_page_title = 'This is a page :)';
        $newpage = $client->addPage(
            $site->id,
            $site->homePage->id,
            null,
            'foo',
            'test-layout',
            1,
            $new_page_title
        );
        $this->assertInstanceOf(Page::class, $newpage);
        $newpage = $client->addPage(
            $site->id,
            $site->homePage->children()->first()->id,
            $site->homePage->id,
            'bar',
            'test-layout',
            1,
            $new_page_title
        );
        $this->assertInstanceOf(Validator::class, $newpage);
    }

    /**
     * @test
     */
    public function addPage_addsAPageBeforeAnotherPage_ifBeforeID()
    {
        $publishing_group_id = factory(PublishingGroup::class)->create()->getKey();
        $client = $this->fixture();
        $site = $client->createSite(
            $publishing_group_id, 'Test Site', 'example.com', '', 'test-layout', 1
        );
        $client->addTree($site->id, $site->homePage->id, null, $this->testTree);
        $parent1 = Page::findByPath('/undergraduate/2018');
        $parent2 = Page::findByPath('/postgraduate/2017');
        $client->addPage(
            $site->id,
            $parent1->parent_id,
            $parent1->id,
            'test',
            'test-layout',
            1,
            'test1'
        );
        $client->addPage(
            $site->id,
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

}