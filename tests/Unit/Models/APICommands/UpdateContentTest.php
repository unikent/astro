<?php

namespace Tests\Unit\Models\APICommands;

use App\Models\APICommands\UpdateContent;

class UpdateContentTest extends APICommandTestCase
{
    public function fixture()
    {
        return new UpdateContent();
    }

    public function getValidData()
    {
        return [];
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifID_isMissingNullOrInvalid_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifID_isNonDraftPage_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifBlocksIsNotPresent_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifBlocksIsNotArray_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifBlocksIsEmptyArray_passes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifBlockHasNoDefinition_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifRegionHasNoDefinition_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifBlockNotAllowedInRegion_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifBlockFailsValidation_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifNestedBlockFailsValidation_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifBlocksPassValidation_passes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifNestedBlocksPassValidation_passes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_createsANewRevisionAndSetsItOnPage()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_bakesModifiedBlocksIntoNewRevision()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_onlyRemovesBlocksFromSpecifiedRegions()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function processBlocks_clearsRegions()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function processBlocks_savesNewBlocks_withRegionNameAndOrder()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function processBlocks_associatesMediaCorrectlyWithBlocks()
    {
        $this->markTestIncomplete();
    }


}