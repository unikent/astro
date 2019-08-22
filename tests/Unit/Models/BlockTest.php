<?php
namespace Tests\Unit\Models;

use Mockery;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Block;
use App\Models\Definitions\Block as BlockDefinition;

class BlockTest extends TestCase
{

	/**
	 * @test
	 */
	public function deleteForPageRegion_WhenPageIsGiven_DeletesAllBlocksForGivenPageAndRegion()
	{

		$page = factory(Page::class)->create();
		factory(Block::class, 3)->create([ 'page_id' => $page->getKey() ]);

		$count = Block::count();

		Block::deleteForPageRegion($page, 'test-region');
		$this->assertEquals($count - 3, Block::count());
	}

	/**
	 * @test
	 */
	public function deleteForPageRegion_WhenPageIdInstanceIsGiven_DeletesAllBlocksForGivenPageAndRegion()
	{

		$page = factory(Page::class)->create();
		factory(Block::class, 3)->create([ 'page_id' => $page->getKey() ]);

		$count = Block::count();

		Block::deleteForPageRegion($page->getKey(), 'test-region');
		$this->assertEquals($count - 3, Block::count());
	}

	/**
	 * @test
	 */
	public function deleteForPageRegion_DoesNotDeleteBlocksInOtherRegions()
	{

		$page = factory(Page::class)->create();

		factory(Block::class, 3)->create([ 'page_id' => $page->getKey() ]);
		factory(Block::class, 2)->create([ 'page_id' => $page->getKey(), 'region_name' => 'foobar' ]);

		$count = Block::count();

		Block::deleteForPageRegion($page, 'foobar');
		$this->assertEquals($count - 2, Block::count());
	}



	/**
	 * @test
	 */
	public function getDefinition_ReturnBlockDefinition(){
		$block = factory(Block::class)->make();
		$this->assertInstanceOf(BlockDefinition::class, $block->getDefinition());
	}

	/**
	 * @test
	 */
	public function getDefinition_WhenBlockDefinitionIsNotLoaded_LoadsSupportedBlockDefinition(){
		$block = factory(Block::class)->make();
		$definition = $block->getDefinition();

		$this->assertNotEmpty($definition);
		$this->assertEquals('test-block', $definition->name);
	}

	/**
	 * @test
	 */
	public function getDefinition_WhenBlockDefinitionIsLoaded_DoesNotReloadBlockDefinition(){
		$block = factory(Block::class)->make();
		$block->getDefinition(); 					// This should populate $blockDefinition

		$block = Mockery::mock($block)->makePartial()->shouldAllowMockingProtectedMethods();
		$block->shouldNotReceive('loadBlockDefinition');

		$definition = $block->getDefinition(); 	// This should not re-populate $blockDefinition
		$this->assertNotEmpty($definition);				// Is populated, but not empty.
	}


	/**
	 * @test
	 * @dataProvider providerDynamicOptions
	 *
	 */
	public function getDynamicOptionsReturnsDynamicOptionsInExpectedFormat($labelField, $valueField, $apiResponseBody, $expectedOptions)
	{
		$fakeHttpClient = new class($apiResponseBody) {

			protected $data;

			public function __construct($data) {
				$this->data = $data;
			}
			public function get($url, $cachetime) {
				return $this->data;
			}
		};

		$url = 'https://endpoint.com';
		$cacheTime = 10;

		$options = BlockDefinition::getDynamicOptions(
			$url,
			$labelField,
			$valueField,
			$cacheTime,
			$fakeHttpClient
		);

		$this->assertEquals($expectedOptions, $options);
	}

	public function providerDynamicOptions()
	{
		return [
			'subject categories' => [
				$labelField = 'id',
				$valueField = 'name',
				$apiResponseBody = <<< JSON
{
  "1": {
    "id": "1",
    "created_at": "2012-12-17 16:57:01",
    "updated_at": "2012-12-17 16:57:01",
    "name": "American Studies",
    "hidden": "0"
  },
  "2": {
    "id": "2",
    "created_at": "2012-12-17 16:57:19",
    "updated_at": "2012-12-17 16:57:19",
    "name": "Anthropology and Conservation",
    "hidden": "0"
  }
}
JSON
				,$expectedOptions = [
					[
						'value' => 'American Studies',
						'label' => '1',
					],
					[
						'value' => 'Anthropology and Conservation',
						'label' => '2',
					]
				],
			],

			'the doctors who' => [
				$labelField = 'actor',
				$valueField = 'doctor',
				$apiResponseBody = <<< JSON
{
  "1": {
    "actor": "William Hartnell",
	"doctor": "1",
    "first_episode": "An Unearthly Child",
	"final_episode": "The Tenth Planet",
	"year": "1963"
  },
  "2": {
    "actor": "Peter Davison",
	"doctor": "5",
    "first_episode": "Castrovalva",
	"final_episode": "The Caves of Androzani",
	"year": "1981"
  },
  "3": {
    "actor": "Paul McGann",
	"doctor": "8",
    "first_episode": "Doctor Who (tv movie)",
	"final_episode": "The Night of the Doctor",
	"year": "1996"
  }
}
JSON
				,$expectedOptions = [
					[
						'value' => '1',
						'label' => 'William Hartnell',
					],
					[
						'value' => '5',
						'label' => 'Peter Davison',
					],
					[
						'value' => '8',
						'label' => 'Paul McGann',
					]
				],
			],

			'wrong label' => [
				$labelField = 'school',
				$valueField = 'doctor',
				$apiResponseBody = <<< JSON
{
  "1": {
    "actor": "William Hartnell",
	"doctor": "1",
    "first_episode": "An Unearthly Child",
	"final_episode": "The Tenth Planet",
	"year": "1963"
  }
}
JSON
				,$expectedOptions = []

			],

			'wrong value' => [
				$labelField = 'actor',
				$valueField = 'price',
				$apiResponseBody = <<< JSON
{
  "1": {
    "actor": "William Hartnell",
	"doctor": "1",
    "first_episode": "An Unearthly Child",
	"final_episode": "The Tenth Planet",
	"year": "1963"
  }
}
JSON
				,$expectedOptions = []

			]
		];
	}
}
