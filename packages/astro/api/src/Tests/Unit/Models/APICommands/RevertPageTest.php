<?php

namespace Astro\API\Tests\Unit\Models\APICommands;

/**
 * @todo This is just a placeholder test to hold some tests that from the old refactored model that are not
 * needed yet, but may be soon...
 * @package Tests\Unit\Models\APICommands
 */
class RevertPageTest extends APICommandTestCase
{
    public function command()
    {
        return new RevertPage();
    }

    public function getValidData()
    {
        return [

        ];
    }

    /**
     * @group ignore
     */
    public function revert_WhenPublishedPageIsNotAssociatedWithPage_ThrowsException()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $r1 = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
        $r1->page->publish(new PageTransformer);

        $r2 = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
        $r2->page->publish(new PageTransformer);

        $this->expectException(Exception::class);
        $r1->page->revert($r2->page->published);
    }

    /**
     * @group ignore
     */
    public function revert_RevertsPageToMatchPublishedPage()
    {
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $route = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
        $page = $route->page;

        $page->publish(new PageTransformer);

        $title = $page->title;
        $page->title = 'Foobar';

        $layout = $page->layout_name;
        $page->layout_name = 'fizzbuzz17';

        $page->save();
        $this->assertEquals('Foobar', $page->title);
        $this->assertEquals('fizzbuzz17', $page->layout_name);

        $page->revert($page->published);
        $this->assertEquals($title, $page->title);
        $this->assertEquals($layout, $page->layout_name);
    }

    /**
     * @group ignore
     */
    public function revert_RevertsBlocksToMatchPublishedPage()
    {
        return $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $route = factory(Page::class)->states([ 'withPage', 'isRoot' ])->create();
        $page = $route->page;

        $blocks = factory(Block::class, 2)->create([ 'page_id' => $page->getKey(), 'region_name' => 'test-region' ]);
        $page->publish(new PageTransformer);

        $moreBlocks = factory(Block::class, 3)->create([ 'page_id' => $page->getKey(), 'region_name' => 'test-region' ]);

        $page = $page->fresh();
        $this->assertCount(5, $page->blocks);

        $page->revert($page->published);
        $this->assertCount(2, $page->blocks);
    }

}