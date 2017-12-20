<?php
namespace Astro\API\Tests\Unit\Models\Definitions;

use Config;
use Mockery;
use Astro\API\Tests\TestCase;
use Illuminate\Support\Collection;
use Astro\API\Models\Definitions\Layout;
use Astro\API\Exceptions\DefinitionNotFoundException;

class LayoutTest extends TestCase
{

	public function setUp(){
		parent::setUp();
		Config::set('app.definitions_path', __DIR__ . '/../../../Support/Fixtures/definitions');
	}

	/**
	 * @test
	 */
	public function locateDefinition_WhenDefinitionIsFound_ReturnsPath(){
		$path = Layout::locateDefinition('test-layout-v1');
		$this->assertEquals(__DIR__ . '/../../../Support/Fixtures/definitions/layouts/test-layout/v1/definition.json', $path);
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
		$path = Layout::locateDefinitionOrFail('test-layout-v1');
		$this->assertEquals(__DIR__ . '/../../../Support/Fixtures/definitions/layouts/test-layout/v1/definition.json', $path);
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
		$path = Layout::locateDefinition('test-layout-v1');
		$layout = Layout::fromDefinitionFile($path);

		$this->assertInstanceOf(Collection::class, $layout->getRegionDefinitions());
	}

	/**
	 * @test
	 * @group focus
	 * Note: the 'test-layout' fixture only supports 'test-region'
	 */
	public function getRegionDefinitions_WhenRegionDefinitionsAreNotLoaded_LoadsSupportedRegionDefinitionsIntoCollection(){
		$path = Layout::locateDefinition('test-layout-v1');
		$layout = Layout::fromDefinitionFile($path);

		$collection = $layout->getRegionDefinitions();

		$this->assertCount(1, $collection);
		$this->assertEquals('test-region', $collection[0]->name);
	}

	/**
	 * @test
	 * Note: the 'test-layout' fixture only supports 'test-region'
	 */
	public function getRegionDefinitions_WhenRegionDefinitionsAreLoaded_DoesNotReloadRegionDefinitions(){
		$path = Layout::locateDefinition('test-layout-v1');

		$layout = Layout::fromDefinitionFile($path);
		$layout->getRegionDefinitions(); 				// This should populate the Collection

		$layout = Mockery::mock($layout)->makePartial()->shouldAllowMockingProtectedMethods();
		$layout->shouldNotReceive('loadRegionDefinitions');

		$collection = $layout->getRegionDefinitions(); 	// This should not populate the Collection
		$this->assertNotEmpty($collection);				// Is populated, but not empty.
	}

}
