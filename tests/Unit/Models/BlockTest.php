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

}
