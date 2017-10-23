<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/07/17
 * Time: 11:27
 */

namespace Tests\Unit\Models\APICommands;

use App\Models\APICommands\DeletePage;
use App\Models\Contracts\APICommand;
use App\Models\PublishingGroup;

class DeletePageTest extends APICommandTestCase
{
    protected $pubgroup = null;
    protected $site = null;

    public function getValidData()
    {
        $this->pubgroup = $this->pubgroup ? $this->pubgroup : factory(PublishingGroup::class)->create();
        $this->site = $this->site ? $this->site : $this->api()->createSite($this->pubgroup->id,'Test Site', 'example.org', null, [
            'name' => 'test-layout',
            'version' => 1
        ]);
        $page = $this->api()->addPage($this->site->draftHomepage->id, null, 'foo', $this->test_layout, 'Foo');
        return [
            'id' => $page->id
        ];
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPageID_isValid_passes()
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
    public function execute_createsADeletedPage_forPageAndItsDescendants()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_deletesPage_andItsDescendants()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_deletesPage_whenOnlyChild()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_deletesPage_whenSiblings()
    {
       $this->markTestIncomplete();
    }

    /**
     * @return APICommand A new instance of the class to test.
     */
    public function command()
    {
        return new DeletePage();
    }
}
