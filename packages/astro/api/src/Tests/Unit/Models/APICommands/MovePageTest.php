<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/07/17
 * Time: 11:27
 */

namespace Astro\API\Tests\Unit\Models\APICommands;

use Astro\API\Models\APICommands\MovePage;
use Astro\API\Models\Contracts\APICommand;

class MovePageTest extends APICommandTestCase
{
    protected $site = null;

    public function getValidData()
    {
        $this->site = $this->site ? $this->site : $this->api()->createSite('Test Site', 'example.org', null, [
            'name' => 'one-page-site',
            'version' => 1
        ]);
        $page = $this->api()->addPage($this->site->draftHomepage->id, null, 'foo', $this->test_layout, 'Foo');
        return [
            'id' => $page->id,
            'parent_id' => $this->site->draftHomepage->id
        ];
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenDataisValid_passes()
    {
        $validator = $this->validator($this->input(null));
        $validator->passes();
        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageID_isMissingOrInvalid_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageID_isHomepage_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPage_existsButIsNotDraft_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenParentIsMissing_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenParentIsNotDraft_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenParentIsNotSameSiteAsPage_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenParentIsADescendantOfPage_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenParentIsPage_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenNextIsInvalid_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenNextIsPresentButDoesNotShareParent_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenNextIsPage_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenNextIsPresent_ButNotDraft_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenNextIsPresent_ButNotSameSite_fails()
    {
        $this->markTestIncomplete();
    }


    /**
     * @test
     * @group APICommands
     */
    public function execute_movesPage_andItsDescendants()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_movesPage_whenOnlyChild()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_MovesPage_whenSiblings()
    {
       $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function getRedirects_returnsArrayWithOnePathAndPageIDItem_forLeafPage()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function getRedirects_returnsArrayWithEntriesForPageAndAllDescendantsOnly()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function updatePaths_updatesPaths_whenMovedPageHasChildren_andMoves()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function updatePaths_updatesPaths_whenMovedPageHasChildren_andMovesWithNoNext()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function updatePaths_updatesPaths_whenMovedPageHasChildren_andIsReordered()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function updatePaths_updatesPaths_whenMovedPageIsLeaf_andMoves()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function updatePaths_updatesPaths_whenMovedPageIsLeaf_andMovesWithNoNext()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function updatePaths_updatesPaths_whenMovedPageIsLeaf_andIsReordered()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_movesPage_withNoNext_movesPageAsLastChild()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_movesPage_withNext_andSameParent_movesPageAsPreviousSibling()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_movesPage_withNext_andDifferentParent_movesPageAsPreviousSibling()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return APICommand A new instance of the class to test.
     */
    public function command()
    {
        return new MovePage();
    }
}
