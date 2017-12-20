<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/07/17
 * Time: 11:27
 */

namespace Astro\API\Tests\Unit\Models\APICommands;

use Astro\API\Models\APICommands\DeletePage;
use Astro\API\Models\Contracts\APICommand;

class DeletePageTest extends APICommandTestCase
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
