<?php
/**
* Created by PhpStorm.
* User: sam
* Date: 27/07/17
* Time: 11:27
*/

namespace Astro\API\Tests\Unit\Models\APICommands;

use Astro\API\Models\APICommands\UpdateSite;
use Astro\API\Models\Contracts\APICommand;
use Astro\API\Models\LocalAPIClient;
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
        $site = $api->createSite("Test", "kent.ac.uk", "", [ "name" => "one-page-site", "version" => 1]);

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
        $site = $api->createSite("Test", "kent.ac.uk", "", [ "name" => "one-page-site", "version" => 1]);

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
        $site = $api->createSite("Test", "kent.ac.uk", "", [ "name" => "one-page-site", "version" => 1]);

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
        $site = $api->createSite("Test", "kent.ac.uk", "", [ "name" => "one-page-site", "version" => 1]);
        
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
        $site = $api->createSite("Test", "kent.ac.uk", "", [ "name" => "one-page-site", "version" => 1]);
        
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
        // given we have a site with an original name
        $originalName = 'Test Site Name';
        $api = new LocalAPIClient(factory(\App\Models\User::class)->create());
        $site = $api->createSite( $originalName, "kent.ac.uk", "", [ "name" => "one-page-site", "version" => 1]);
        
        // and we change the name
        $updatedName = 'Updated Test Site Name';
        $site = $this->command()->execute(new Collection(['id' => $site->id, 'name' => $updatedName]), factory(User::class)->create());
        
        // then the name of the site should be changed
        $this->assertNotEquals($originalName, $site['name']);
        $this->assertEquals($updatedName, $site['name']);
    }

    /**
    * @test
    * @group APICommands
    */
    public function execute_withPath_updatesPathField()
    {
        // given we have a site with an original path
        $originalPath = "/original";
        $api = new LocalAPIClient(factory(\App\Models\User::class)->create());
        $site = $api->createSite('Site Name', "kent.ac.uk", $originalPath, [ "name" => "one-page-site", "version" => 1]);

        // and we change the path
        $updatedPath = "/updated";
        $site = $this->command()->execute(new Collection(['id' => $site->id, 'path' => $updatedPath]), factory(User::class)->create());

        // then the path of the site should be changed
        $this->assertNotEquals($originalPath, $site['path']);
        $this->assertEquals($updatedPath, $site['path']);
    }
    
    /**
    * @test
    * @group APICommands
    */
    public function execute_withHost_updatesHostField()
    {
        // given we have a site with an original path
        $originalHost = "lancaster.ac.uk";
        $api = new LocalAPIClient(factory(\App\Models\User::class)->create());
        $site = $api->createSite('Site Name', $originalHost, "", [ "name" => "one-page-site", "version" => 1]);
        
        // and we change the path
        $updatedHost = "kent.ac.uk";
        $site = $this->command()->execute(new Collection(['id' => $site->id, 'host' => $updatedHost]), factory(User::class)->create());
        
        // then the host of the site should be changed
        $this->assertNotEquals($originalHost, $site['host']);
        $this->assertEquals($updatedHost, $site['host']);
    }
    
    /**
    * @test
    * @group APICommands
    */
    public function execute_returnsSiteThatWasModified()
    {
         // given we have a site
         $api = new LocalAPIClient(factory(\App\Models\User::class)->create());
         $site = $api->createSite('Site Name', 'kent.ac.uk', "", [ "name" => "one-page-site", "version" => 1]);

         // when we update a field
         $updatedName = 'Redesigned Site Name';
         $updatedSite = $this->command()->execute(new Collection(['id' => $site->id, 'name' => $updatedName]), factory(User::class)->create());
         
         // the site we are returned should be the same site
         $this->assertEquals($site->id, $updatedSite->id);
    } 
    
    /**
    * @return APICommand A new instance of the class to test.
    */
    public function command()
    {
        return new UpdateSite();
    }
}
