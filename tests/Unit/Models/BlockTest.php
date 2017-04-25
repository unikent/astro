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

		Block::deleteForPageRegion($page, 'test-region');
		$this->assertEquals(0, Block::count());
	}

	/**
	 * @test
	 */
	public function deleteForPageRegion_WhenPageIdInstanceIsGiven_DeletesAllBlocksForGivenPageAndRegion()
	{
		$page = factory(Page::class)->create();
		factory(Block::class, 3)->create([ 'page_id' => $page->getKey() ]);

		Block::deleteForPageRegion($page->getKey(), 'test-region');
		$this->assertEquals(0, Block::count());
	}

	/**
	 * @test
	 */
	public function deleteForPageRegion_DoesNotDeleteBlocksInOtherRegions()
	{
		$page = factory(Page::class)->create();

		factory(Block::class, 3)->create([ 'page_id' => $page->getKey() ]);
		factory(Block::class, 2)->create([ 'page_id' => $page->getKey(), 'region_name' => 'foobar' ]);

		Block::deleteForPageRegion($page, 'foobar');
		$this->assertEquals(3, Block::count());
	}



	/**
	 * @test
	 */
	public function getBlockDefinition_ReturnBlockDefinition(){
		$block = factory(Block::class)->make();
		$this->assertInstanceOf(BlockDefinition::class, $block->getBlockDefinition());
	}

	/**
	 * @test
	 */
	public function getBlockDefinition_WhenBlockDefinitionIsNotLoaded_LoadsSupportedBlockDefinition(){
		$block = factory(Block::class)->make();
		$definition = $block->getBlockDefinition();

		$this->assertNotEmpty($definition);
		$this->assertEquals('test-block', $definition->name);
	}

	/**
	 * @test
	 */
	public function getBlockDefinition_WhenBlockDefinitionIsLoaded_DoesNotReloadBlockDefinition(){
		$block = factory(Block::class)->make();
		$block->getBlockDefinition(); 					// This should populate $blockDefinition

		$block = Mockery::mock($block)->makePartial()->shouldAllowMockingProtectedMethods();
		$block->shouldNotReceive('loadBlockDefinition');

		$definition = $block->getBlockDefinition(); 	// This should not re-populate $blockDefinition
		$this->assertNotEmpty($definition);				// Is populated, but not empty.
	}



	/**
	 * @test
	 */
	public function toArray_WhenBlockDefinitionIsNotLoaded_DoesNotIncludeBlockDefinition()
	{
		$block = factory(Block::class)->make();

		$output = $block->toArray();
		$this->assertArrayNotHasKey('blockDefinition', $output);
	}

	/**
	 * @test
	 */
	public function toArray_WhenBlockDefinitionIsLoaded_IncludesBlockDefinition()
	{
		$block = factory(Block::class)->make();
		$block->loadBlockDefinition();

		$output = $block->toArray();
		$this->assertArrayHasKey('blockDefinition', $output);
		$this->assertNotEmpty($output['blockDefinition']);
	}


}
