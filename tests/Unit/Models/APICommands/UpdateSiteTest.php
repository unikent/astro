<?php
/**
* Created by PhpStorm.
* User: sam
* Date: 27/07/17
* Time: 11:27
*/

namespace Tests\Unit\Models\APICommands;

use App\Models\APICommands\UpdateSite;
use App\Models\Contracts\APICommand;
use App\Models\LocalAPIClient;
use App\Models\PublishingGroup;
use App\Models\User;
use Illuminate\Support\Collection;

class UpdateSiteTest extends APICommandTestCase
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
    public function validation_whenValidSiteButNoFieldsAreProvided_fails()
    {
        $api = new LocalAPIClient(factory(\App\Models\User::class)->create());
        $pubgroup = PublishingGroup::create(['name' => 'test']);
        $site = $api->createSite($pubgroup->id, "Test", "kent.ac.uk", "", [ "name" => "test-layout", "version" => 1]);

        $validator = $this->validator(['id' => $site->id] );
        $this->assertTrue($validator->fails());
    }
    
    /**
    * @test
    * @group APICommands
    */
    public function validation_whenOptionsIsPresentButNotArray_fails()
    {
        $api = new LocalAPIClient(factory(\App\Models\User::class)->create());
        $pubgroup = PublishingGroup::create(['name' => 'test']);
        $site = $api->createSite($pubgroup->id, "Test", "kent.ac.uk", "", [ "name" => "test-layout", "version" => 1]);

        $validator = $this->validator([
            'id' => $site->id,
            'options' => 'this should be an array',
        ]);

        // errors array contains error for options and validation fails
        $this->assertNotEmpty($validator->errors()->get('options'));
        $this->assertTrue($validator->fails());
    }
    
     /**
    * @test
    * @group APICommands
    */
    public function validation_whenOptionsIsArray_passes()
    {
        $api = new LocalAPIClient(factory(\App\Models\User::class)->create());
        $pubgroup = PublishingGroup::create(['name' => 'test']);
        $site = $api->createSite($pubgroup->id, "Test", "kent.ac.uk", "", [ "name" => "test-layout", "version" => 1]);

        $validator = $this->validator([
            'id' => $site->id,
            'options' => [
                'option' => 'value'
            ],
        ]);
        $this->assertTrue($validator->passes());
    }

    /**
    * @test
    * @group APICommands
    */
    public function execute_withOptions_onlyModifiesOptions_inOptionsArray()
    {
        // given we have a site
        $api = new LocalAPIClient(factory(\App\Models\User::class)->create());
        $pubgroup = PublishingGroup::create(['name' => 'test']);
        $site = $api->createSite($pubgroup->id, "Test", "kent.ac.uk", "", [ "name" => "test-layout", "version" => 1]);
        
        // with some options
        $orignalOptions = [
            'keep_this_key_1' => 1,
            'modify_this_key' => 2,
            'keep_this_key_2' => 3
        ];
        $site = $this->command()->execute(new Collection(['id' => $site->id, 'options' => $orignalOptions]), factory(User::class)->create());
        
        // request to modify one of the options
        $updatedOptions = [
            'modify_this_key' => 'changed value',
        ];
        $site = $this->command()->execute(new Collection(['id' => $site->id, 'options' => $updatedOptions]), factory(User::class)->create());
       
        // then the options are unchanged apart from the removal of the unset option
        $this->assertEquals(count($orignalOptions) , count($site['options']));
        $this->assertEquals($orignalOptions['keep_this_key_1'], $site['options']['keep_this_key_1']);
        $this->assertEquals($orignalOptions['keep_this_key_2'], $site['options']['keep_this_key_2']);
        $this->assertNotEquals($orignalOptions['modify_this_key'], $site['options']['modify_this_key']);
    }
    /**
    * @test
    * @group APICommands
    */
    public function execute_withOptions_removesOptionsWhereNull()
    {
        // given we have a site
        $api = new LocalAPIClient(factory(\App\Models\User::class)->create());
        $pubgroup = PublishingGroup::create(['name' => 'test']);
        $site = $api->createSite($pubgroup->id, "Test", "kent.ac.uk", "", [ "name" => "test-layout", "version" => 1]);
        
        // with some options
        $orignalOptions = [
            'keep_this_key_1' => 1,
            'remove_this_key' => 2,
            'keep_this_key_2' => 3
        ];
        $site = $this->command()->execute(new Collection(['id' => $site->id, 'options' => $orignalOptions]), factory(User::class)->create());
        
        // request to unset one of the options
        $updatedOptions = [
            'remove_this_key' => null,
        ];
        $site = $this->command()->execute(new Collection(['id' => $site->id, 'options' => $updatedOptions]), factory(User::class)->create());
        
        // then we have one fewer options and rest are unchanged        
        $this->assertEquals((count($orignalOptions) - 1), count($site['options']));
        $this->assertEquals($orignalOptions['keep_this_key_1'], $site['options']['keep_this_key_1']);
        $this->assertEquals($orignalOptions['keep_this_key_2'], $site['options']['keep_this_key_2']);
        $this->assertFalse(array_key_exists('remove_this_key', $site['options']));
    }
    
    /**
    * @test
    * @group APICommands
    */
    public function execute_withName_updatesNameField()
    {
        $this->markTestIncomplete();
    }
    /**
    * @test
    * @group APICommands
    */
    public function execute_withPath_updatesPathField()
    {
        $this->markTestIncomplete();
    }
    
    /**
    * @test
    * @group APICommands
    */
    public function execute_withHost_updatesHostField()
    {
        $this->markTestIncomplete();
    }
    
    /**
    * @test
    * @group APICommands
    */
    public function execute_withPublishingGroup_updatesPublishingGroupField()
    {
        $this->markTestIncomplete();
    }
    
    
    /**
    * @test
    * @group APICommands
    */
    public function execute_returnsSiteThatWasModified()
    {
        $this->markTestIncomplete();
    }
    
    /**
    * @return APICommand A new instance of the class to test.
    */
    public function command()
    {
        return new UpdateSite();
    }
}
