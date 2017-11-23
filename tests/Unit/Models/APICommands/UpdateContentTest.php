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
     * @expectedException     Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function validateBlock_ifBlockHasNoDefinition_fails()
    {
        $update_content = new UpdateContent();
        $update_content->validateBlock([
                    "definition_name" => "non-existent-test-block"
                ]);
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
    public function validation_ifBlockNotAllowedInSection_fails()
    {
        $valid_data = $this->input(null);

        $valid_data['blocks']['test-region'][0]['blocks'] = [
                [
                    "definition_name" => "another-test-block"
                ]
            ];          
        $validator = $this->validator($valid_data);
        $this->assertFalse($validator->passes());
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
    public function validation_ifSectionNotOptionalAndBlocksEmptyValidation_fails()
    {
        $valid_data = $this->input(null);

        $valid_data['blocks']['test-region-with-required-section'] = $valid_data['blocks']['test-region'];
        unset($valid_data['blocks']['test-region']);

        $valid_data['blocks']['test-region-with-required-section'][0]['blocks'] = [];

        $validator = $this->validator($valid_data);
        $this->assertFalse($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifSectionOptionalAndBlocksEmptyValidation_passes()
    {
        $valid_data = $this->input(null);

        $valid_data['blocks']['test-region'][0]['blocks'] = [];

        $validator = $this->validator($valid_data);
        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifSectionHasTooFewBlocksValidation_fails()
    {
        $valid_data = $this->input(null);

        unset($valid_data['blocks']['test-region'][0]['blocks'][1]);
        unset($valid_data['blocks']['test-region'][0]['blocks'][2]);

        $validator = $this->validator($valid_data);
        $this->assertFalse($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifSectionHasTooManyBlocksValidation_fails()
    {
        $invalid_data = $this->input(null);

        // our sample page data input has 3 blocks in it, so we'll add 5 more 
        // so that it goes over the max amount of permitted blocks in our definition
        $invalid_data['blocks']['test-region'][0]['blocks'][] = $invalid_data['blocks']['test-region'][0]['blocks'][1];
        $invalid_data['blocks']['test-region'][0]['blocks'][] = $invalid_data['blocks']['test-region'][0]['blocks'][1];
        $invalid_data['blocks']['test-region'][0]['blocks'][] = $invalid_data['blocks']['test-region'][0]['blocks'][1];
        $invalid_data['blocks']['test-region'][0]['blocks'][] = $invalid_data['blocks']['test-region'][0]['blocks'][1];
        $invalid_data['blocks']['test-region'][0]['blocks'][] = $invalid_data['blocks']['test-region'][0]['blocks'][1];
        $invalid_data['blocks']['test-region'][0]['blocks'][] = $invalid_data['blocks']['test-region'][0]['blocks'][1];


        $validator = $this->validator($invalid_data);
        $this->assertFalse($validator->passes());
    }

    /**
     * This test is covered by validation_whenInput_isValid_passes
     * @group APICommands
     */
    public function validation_ifSectionHasRightNumberOfBlocksValidation_passes()
    {
        // this is just here for completeness. Our sample valid_data should already 
        // be valid against our definition
    }

    /**
     * This test is covered by validation_whenInput_isValid_passes
     * @group APICommands
     */
    public function validation_ifSectionsInRegionMatchSectionsInDefinitionValidation_passes()
    {
        // this is just here for completeness. Our sample valid_data should already 
        // be valid against our definition
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifTooManySectionsInRegionValidation_fails()
    {
        $invalid_data = $this->input(null);
        $invalid_data['blocks']['test-region'][] = $invalid_data['blocks']['test-region'][0];

        $validator = $this->validator($invalid_data);
        $this->assertFalse($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifTooFewSectionsInRegionValidation_fails()
    {
        $invalid_data = $this->input(null);
        unset($invalid_data['blocks']['test-region'][0]);

        $validator = $this->validator($invalid_data);
        $this->assertFalse($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_ifSectionsInRegionAreInWrongOrderValidation_fails()
    {
        $invalid_data = $this->input(null);

        // change the region definition being used to one with multiple sections
        $invalid_data['blocks']['test-region-with-multiple-sections'] = $invalid_data['blocks']['test-region'];
        unset($invalid_data['blocks']['test-region']);

        // add a second (permitted) section but in the wrong order
        $invalid_data['blocks']['test-region-with-multiple-sections'][] = $invalid_data['blocks']['test-region-with-multiple-sections'][0];
        $invalid_data['blocks']['test-region-with-multiple-sections'][0]['name'] = 'test-section2';


        $validator = $this->validator($invalid_data);
        $this->assertFalse($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     * @group wip
     */
    public function validation_ifUnknownSectionsFoundInRegionValidation_fails()
    {
        $invalid_data = $this->input(null);

        $invalid_data['blocks']['test-region'][0]['name'] = 'unknown-section';

        $validator = $this->validator($invalid_data);
        $this->assertFalse($validator->passes());
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