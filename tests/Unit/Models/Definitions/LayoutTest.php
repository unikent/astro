<?php
namespace Tests\Unit\Models\Definitions;

use Config;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Collection;
use App\Models\Definitions\Layout;
use App\Exceptions\DefinitionNotFoundException;

class LayoutTest extends TestCase
{

	public function setUp(){
		parent::setUp();
		Config::set('app.definitions_path', base_path('tests/Support/Fixtures/definitions'));
	}

	/**
	 * @test
	 */
	public function locateDefinition_WhenDefinitionIsFound_ReturnsPath(){
		$path = Layout::locateDefinition('test-layout');
		$this->assertEquals(base_path('tests/Support/Fixtures/definitions/layouts/test-layout/v1/definition.json'), $path);
	}

	/**
	 * @test
	 */
	public function locateDefinition_WhenDefinitionIsNotFound_ReturnsNull(){
		$this->assertNull(Layout::locateDefinition('foobar'));
	}

	/**
	 * @test
	 */
	public function locateDefinitionOrFail_WhenDefinitionIsFound_ReturnsPath(){
		$path = Layout::locateDefinitionOrFail('test-layout');
		$this->assertEquals(base_path('tests/Support/Fixtures/definitions/layouts/test-layout/v1/definition.json'), $path);
	}

	/**
	 * @test
	 */
	public function locateDefinitionOrFail_WhenDefinitionIsNotFound_ThrowsException(){
		$this->expectException(DefinitionNotFoundException::class);
		Layout::locateDefinitionOrFail('foobar');
	}


	/**
	 * @test
	 */
	public function getRegionDefinitions_ReturnsCollection(){
		$path = Layout::locateDefinition('test-layout');
		$region = Layout::fromDefinitionFile($path);

		$this->assertInstanceOf(Collection::class, $region->getRegionDefinitions());
	}

	/**
	 * @test
	 * @group focus
	 * Note: the 'test-layout' fixture only supports 'test-region'
	 */
	public function getRegionDefinitions_WhenRegionDefinitionsAreNotLoaded_LoadsSupportedRegionDefinitionsIntoCollection(){
		$path = Layout::locateDefinition('test-layout');
		$region = Layout::fromDefinitionFile($path);

		$collection = $region->getRegionDefinitions();

		$this->assertCount(1, $collection);
		$this->assertEquals('test-region', $collection[0]->name);
	}

	/**
	 * @test
	 * Note: the 'test-layout' fixture only supports 'test-region'
	 */
	public function getRegionDefinitions_WhenRegionDefinitionsAreLoaded_DoesNotReloadRegionDefinitions(){
		$path = Layout::locateDefinition('test-layout');

		$region = Layout::fromDefinitionFile($path);
		$region->getRegionDefinitions(); 				// This should populate the Collection

		$region = Mockery::mock($region)->makePartial()->shouldAllowMockingProtectedMethods();
		$region->shouldNotReceive('loadRegionDefinitions');

		$collection = $region->getRegionDefinitions(); 	// This should not populate the Collection
		$this->assertNotEmpty($collection);				// Is populated, but not empty.
	}

}
