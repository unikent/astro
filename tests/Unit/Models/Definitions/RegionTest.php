<?php
namespace Tests\Unit\Models\Definitions;

use Config;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Collection;
use App\Models\Definitions\Region;
use App\Exceptions\DefinitionNotFoundException;

class RegionTest extends TestCase
{

	public function setUp(){
		parent::setUp();
		Config::set('app.definitions_path', base_path('tests/Support/Fixtures/definitions'));
	}

	/**
	 * @test
	 */
	public function locateDefinition_WhenDefinitionIsFound_ReturnsPath(){
		$path = Region::locateDefinition('test-region');
		$this->assertEquals(base_path('tests/Support/Fixtures/definitions/regions/test-region/v1/definition.json'), $path);
	}

	/**
	 * @test
	 */
	public function locateDefinition_WhenDefinitionIsNotFound_ReturnsNull(){
		$this->assertNull(Region::locateDefinition('foobar'));
	}

	/**
	 * @test
	 */
	public function locateDefinitionOrFail_WhenDefinitionIsFound_ReturnsPath(){
		$path = Region::locateDefinitionOrFail('test-region');
		$this->assertEquals(base_path('tests/Support/Fixtures/definitions/regions/test-region/v1/definition.json'), $path);
	}

	/**
	 * @test
	 */
	public function locateDefinitionOrFail_WhenDefinitionIsNotFound_ThrowsException(){
		$this->expectException(DefinitionNotFoundException::class);
		Region::locateDefinitionOrFail('foobar');
	}


	/**
	 * @test
	 */
	public function getBlockDefinitions_ReturnsCollection(){
		$path = Region::locateDefinition('test-region');
		$region = Region::fromDefinitionFile($path);

		$this->assertInstanceOf(Collection::class, $region->getBlockDefinitions());
	}

	/**
	 * @test
	 * @group focus
	 * Note: the 'test-region' fixture only supports 'test-block'
	 */
	public function getBlockDefinitions_WhenBlockDefinitionsAreNotLoaded_LoadsSupportedBlockDefinitionsIntoCollection(){
		$path = Region::locateDefinition('test-region');
		$region = Region::fromDefinitionFile($path);

		$collection = $region->getBlockDefinitions();

		$this->assertCount(1, $collection);
		$this->assertEquals('test-block', $collection[0]->name);
	}

	/**
	 * @test
	 * Note: the 'test-region' fixture only supports 'test-block'
	 */
	public function getBlockDefinitions_WhenBlockDefinitionsAreLoaded_DoesNotReloadBlockDefinitions(){
		$path = Region::locateDefinition('test-region');

		$region = Region::fromDefinitionFile($path);
		$region->getBlockDefinitions(); 				// This should populate the Collection

		$region = Mockery::mock($region)->makePartial()->shouldAllowMockingProtectedMethods();
		$region->shouldNotReceive('loadBlockDefinitions');

		$collection = $region->getBlockDefinitions(); 	// This should not populate the Collection
		$this->assertNotEmpty($collection);				// Is populated, but not empty.
	}

}
