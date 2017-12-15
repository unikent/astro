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

use DatabaseTransactions;

class UpdatePageTest extends APICommandTestCase
{

	public $pageToBeUpdated;
	public $faker;

	public function setup()
	{
		parent::setup();
		$this->faker = \Faker\Factory::create();
		$this->pageToBeUpdated = factory(Page::class)->create();
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
