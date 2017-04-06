<?php
namespace Tests\Unit\Models\Definitions;

use Config;
use Tests\TestCase;
use App\Models\Definitions\Block;

class BlockTest extends TestCase
{

	public function setUp(){
		parent::setUp();
		Config::set('app.definitions_path', base_path('tests/Support/Fixtures/definitions'));
	}

	/**
	 * @test
	 */
	public function locateDefinition_WhenDefinitionIsFound_ReturnsPath(){
		$path = Block::locateDefinition('test-block');
		$this->assertEquals(base_path('tests/Support/Fixtures/definitions/blocks/test-block/v1/definition.json'), $path);
	}

	/**
	 * @test
	 */
	public function locateDefinition_WhenDefinitionIsNotFound_ReturnsNull(){
		$this->assertNull(Block::locateDefinition('foobar'));
	}

}
