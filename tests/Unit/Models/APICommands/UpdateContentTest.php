<?php

namespace Tests\Unit\Models\APICommands;

use App\Models\APICommands\UpdateContent;

class UpdateContentTest extends APICommandTestCase
{
    public function command()
    {
        return new UpdateContent();
    }

    public function getValidData()
    {
        return json_decode(file_get_contents(base_path('tests/Support/Fixtures/api_requests/v1/update_content.json')), true);
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenInput_isValid_passes()
    {
        $validator = $this->validator($this->input(null));
        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifID_isNullOrInvalid_fails()
    {
        $validator = $this->validator($this->input([
            'id' => null
        ]));
        $this->assertFalse($validator->passes());

        $validator = $this->validator($this->input([
            'id' => 1 //this should not be in the database so would fail
        ]));
        $this->assertFalse($validator->passes());
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
        $validator = $this->validator($this->input(null, ['blocks']));
        $this->assertFalse($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifBlocksIsNotArray_fails()
    {
        $validator = $this->validator($this->input([
            'blocks' => 'a string, not an array'
        ]));
        $this->assertFalse($validator->passes());

        $validator = $this->validator($this->input([
            'blocks' => 50
        ]));
        $this->assertFalse($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifBlocksIsEmptyArray_passes()
    {
        $validator = $this->validator($this->input([
            'blocks' => []
        ]));
        $this->assertTrue($validator->passes());
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