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
		$api = new LocalAPIClient(factory(User::class)->create());
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
