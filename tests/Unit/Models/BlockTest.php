<?php
namespace Tests\Unit\Models;

use Mockery;
use Tests\TestCase;
use App\Models\PageContent;
use App\Models\Block;
use App\Models\Definitions\Block as BlockDefinition;

class BlockTest extends TestCase
{

	/**
	 * @test
	 */
	public function deleteForPageRegion_WhenPageIsGiven_DeletesAllBlocksForGivenPageAndRegion()
	{
		$pagecontent = factory(PageContent::class)->create();
		factory(Block::class, 3)->create([ 'page_content_id' => $pagecontent->getKey() ]);

		$count = Block::count();

		Block::deleteForPageRegion($pagecontent, 'test-region');
		$this->assertEquals($count - 3, Block::count());
	}

	/**
	 * @test
	 */
	public function deleteForPageRegion_WhenPageIdInstanceIsGiven_DeletesAllBlocksForGivenPageAndRegion()
	{
		$page = factory(PageContent::class)->create();
		factory(Block::class, 3)->create([ 'page_content_id' => $page->getKey() ]);

		$count = Block::count();

		Block::deleteForPageRegion($page->getKey(), 'test-region');
		$this->assertEquals($count - 3, Block::count());
	}

	/**
	 * @test
	 */
	public function deleteForPageRegion_DoesNotDeleteBlocksInOtherRegions()
	{
		$page = factory(PageContent::class)->create();

		factory(Block::class, 3)->create([ 'page_content_id' => $page->getKey() ]);
		factory(Block::class, 2)->create([ 'page_content_id' => $page->getKey(), 'region_name' => 'foobar' ]);

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

}
