<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/07/17
 * Time: 11:27
 */

namespace Tests\Unit\Models\APICommands;

use Astro\API\Models\Page;
use Astro\API\Models\APICommands\UpdatePageSlug;
use Astro\API\Models\Contracts\APICommand;

class UpdatePageSlugTest extends APICommandTestCase
{
    public function getValidData()
    {
        return [];
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenInput_isValid_passes()
    {
        $home_page = factory(Page::class)->states('withRevision')->create();
        $child_page = factory(Page::class)->states('withRevision')->create([
            'slug'      => 'valid-slug',
            'parent_id' => $home_page->id,
            'site_id'   => $home_page->site_id
        ]);

        $validator = $this->validator($this->input([
            'slug' => 'valid-slug',
            'id' => $child_page->id
        ]));

        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageDoesNotExist_fails()
    {
        $validator = $this->validator($this->input([
            'slug' => 'valid-slug',
            'id'   => 1
        ]));

        $errors = $validator->errors();

        $this->assertTrue($validator->fails());
        $this->assertTrue($errors->has('id'));
        // "page_is_a_subpage" and "ensure page_is_draft" should both fail
        $this->assertEquals($errors->count('id'), 2);
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageIsNotDraft_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageIsMissingOrNull_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSlugIsMissingNullOrEmpty_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenASiblingPageExistsWithSameSlug_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSlugContainsIllegalCharacters_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSlugIsTooLong_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSlugIsCurrentSlug_passes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSlugAndParentPath_existsInDifferentSite_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_updatesSlugAndPath()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_doesNotCreateANewRevision()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_returnsPageIfSlugIsUnchanged()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_withChangedSlug_returnsPageWithNewSlugAndPath()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_withChangedSlug_onPageWithDescendants_updatesDescendantsPaths()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return APICommand A new instance of the class to test.
     */
    public function command()
    {
        return new UpdatePageSlug();
    }
}
