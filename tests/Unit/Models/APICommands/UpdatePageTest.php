<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/07/17
 * Time: 11:27
 */

namespace Tests\Unit\Models\APICommands;

use App\Models\APICommands\UpdatePage;
use App\Models\Page;
use App\Models\Contracts\APICommand;
use App\Models\LocalAPIClient;

use DatabaseTransactions;

class UpdatePageTest extends APICommandTestCase
{

	public $pageToBeUpdated;
	public $faker;

	public function setup()
	{
		parent::setup();
		$this->faker = \Faker\Factory::create();

		// create and save the page to ensure the blogs are filled with expected data from fixtures
		$this->pageToBeUpdated = factory(Page::class)->states('withRevision')->create();
		$this->pageToBeUpdated->save();
		$this->nonDraftPage = factory(Page::class)->create(['version' => Page::STATE_PUBLISHED]);
	}
	

	public function getValidData()
	{
		return [
			'id' => $this->pageToBeUpdated->id,
			'title' => 'Page Title',
			'options' => []
		];
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
		// we have an existing page which is not draft and we try to update it
		// dd($this->nonDraftPage);
		$validator = $this->validator($this->input(['id' => $this->nonDraftPage->id]));
		$validator->passes();
		$this->assertFalse($validator->passes());
	}

	/**
	 * @test
	 * @group APICommands
	 */
	public function validation_whenPageDoesNotExist_fails()
	{
		// find the id of a page which does not exist...
		$highestPageID = Page::all()->sortBy('id')->pluck('id')->last();
		$nonExistantPageID = $highestPageID + 1;
		$validator = $this->validator($this->input(['id' => $nonExistantPageID]));
		$validator->passes();
		$this->assertFalse($validator->passes());
	}

	/**
	 * @test
	 * @group APICommands
	 */
	public function validation_whenPageIsNull_fails()
	{
		$validator = $this->validator($this->input(['id' => null]));
		$validator->passes();
		$this->assertFalse($validator->passes());
	}

	/**
	 * @test
	 * @group APICommands
	 */
	public function validation_whenOptionsIsPresentButNotArray_fails()
	{
		$validator = $this->validator($this->input(['options' => 'this is not an array']));
		$validator->passes();
		$this->assertFalse($validator->passes());
	}

	/**
	 * @test
	 * @group APICommands
	 */
	public function validation_whenTitleIsOver150Characters_fails()
	{
		$stringOver150Characters = str_pad('a title ', 150, ' more text') .  ' text to take it over 150';
		$validator = $this->validator($this->input(['title' => $stringOver150Characters]));
		$validator->passes();
		$this->assertFalse($validator->passes());
	}

	/**
	 * @test
	 * @group APICommands
	 */
	public function validation_whenTitleIs150Characters_passes()
	{
		$stringof150Characters = str_pad('a title ', 150, ' more text');
		$validator = $this->validator($this->input(['title' => $stringof150Characters]));
		$validator->passes();
		$this->assertTrue($validator->passes());
	}

	/**
	 * @test
	 * @group APICommands
	 */
	public function validation_whenTitleIsUnder150Characters_passes()
	{
		$stringofFewerThan150Characters = $this->faker->text($maxNbChars = 149);
		$validator = $this->validator($this->input(['title' => $stringofFewerThan150Characters]));
		$validator->passes();
		$this->assertTrue($validator->passes());
	}


	/**
	 * @test
	 * @group APICommands
	 */
	public function execute_withOptions_onlyCreatesANewRevisionIdenticalToPreviousRevision_exceptForOptionsTimestampsAndTrackedFields()
	{
		// GIVEN we have an existing page 
		$api = new LocalAPIClient(factory(\App\Models\User::class)->create());

		// NOTE: updating the page bakes in the block data from the fixtures since this isn't done by the factory
		// so we need a bit more setup here to make this a valid test
		$originalPage = $api->updatePage(
			$id = $this->pageToBeUpdated->id,
			$title = 'A title'
		);

		// WHEN we update the page with some new options
		$options = [
			'sausage' 	=> 'meat',
			'apple'		=> 'fruit'
		];
		$updatedPage = $api->updatePage(
			$id = $this->pageToBeUpdated->id,
			$title = 'A title',
			$options = $options
		);

		// THEN we should have a new revision 
		$this->assertNotEquals($updatedPage->revision->id, $originalPage->revision->id);
		
		// WHICH is the same apart from options, id and timestamps
		$updatedRevision = $updatedPage->revision->toArray();
		unset($updatedRevision['id']);
		unset($updatedRevision['options']);
		unset($updatedRevision['created_at']);
		unset($updatedRevision['updated_at']);

		$originalRevision = $originalPage->revision->toArray();
		unset($originalRevision['id']);
		unset($originalRevision['options']);
		unset($originalRevision['created_at']);
		unset($originalRevision['updated_at']);

		$this->assertEquals($updatedRevision, $originalRevision);
	}

	/**
	 * @test
	 * @group APICommands
	 * @group cable
	 */
	public function execute_withOptions_onlyModifiesOptions_inOptionsArray()
	{
		$api = new LocalAPIClient(factory(\App\Models\User::class)->create());
	
		// given we have a page with a revision and some options
		$originalSetOfOptions = [
			'a' => 'original-a',
			'b' => 'original-b',
		];
		$originalPage = $api->updatePage(
			$id = $this->pageToBeUpdated->id,
			$title = '',
			$options = $originalSetOfOptions
		);
	
		// when we update the page options with some new options
		$updatedSetOfOptions = [
			'b' => 'updated-b',
			'c' => 'updated-c'
		];
		$updatedPage = $api->updatePage(
			$id = $this->pageToBeUpdated->id,
			$title = '',
			$options = $updatedSetOfOptions
		);

		// only those options which were specified to be updated should be updated
		foreach ($updatedSetOfOptions as $option => $value) {
			$this->assertEquals($value, $updatedPage->revision['options'][$option]);
		}

		// and each of the original options which were not set to be updated have remained the same
		foreach ($originalSetOfOptions as $option => $value) {
			if (!isset($updatedSetOfOptions[$option])) {
				$this->assertEquals($originalSetOfOptions[$option], $originalPage->revision['options'][$option]);
			}
		}
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
	 * @test
	 * @group APICommands
	 */
	public function execute_withoutTitle_createsNewRevisionWithPreviousTitle()
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
