<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/07/17
 * Time: 11:27
 */

namespace Tests\Unit\Models\APICommands;

use App\Models\APICommands\UpdatePage;
use App\Models\Contracts\APICommand;

class UpdatePageTest extends APICommandTestCase
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
        $validator = $this->validator($this->input(null));
        $validator->passes();
        $this->assertTrue($validator->passes());
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
    public function validation_whenPageDoesNotExist_fails()
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
    public function validation_whenOptionsIsPresentButNotArray_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_withOptions_onlyCreatesANewRevisionIdenticalToPreviousRevision_exceptForOptionsTimestampsAndTrackedFields()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_withOptions_onlyModifiesOptions_inOptionsArray()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_withOptions_removesOptionsWhereNull()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_withTitle_onlyCreatesANewRevisionIdenticalToPreviousRevision_exceptForTitleTimestampsAndTrackedFields()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_withNoActualChangesToData_doesNotCreateANewRevision()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_returnsPageThatWasModified()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return APICommand A new instance of the class to test.
     */
    public function command()
    {
        return new UpdatePage();
    }
}
