<?php
namespace Tests\Unit\Models\Definitions;

use Tests\TestCase;
use App\Exceptions\JsonDecodeException;
use Tests\Support\Doubles\Models\Definitions\Double as DefinitionDouble;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class BaseDefinitionTest extends TestCase
{

	protected $definition;

	public function setUp(){
		parent::setUp();
		$this->definition = base_path('tests/Support/Fixtures/definitions/double-definition.json');
	}

	public function tearDown(){
		unset($this->definition);
		parent::tearDown();
	}


	/**
	 * @test
	 */
	public function fill_SetsFillableAttributes(){
		$definition = new DefinitionDouble;
		$definition->fill([ 'foo' => 'Fizz', 'bar' => 'Buzz' ]);

		$this->assertEquals($definition->foo, 'Fizz');
	}

	/**
	 * @test
	 */
	public function fill_DoesNotSetUnfillableAttributes(){
		$definition = new DefinitionDouble;
		$definition->fill([ 'foo' => 'Fizz', 'bar' => 'Buzz' ]);

		$this->assertNotEquals($definition->bar, 'Buzz');
	}

	/**
	 * @test
	 */
	public function forceFill_SetsFillableAttributes(){
		$definition = new DefinitionDouble;
		$definition->forceFill([ 'foo' => 'Fizz', 'bar' => 'Buzz' ]);

		$this->assertEquals($definition->foo, 'Fizz');
	}

	/**
	 * @test
	 */
	public function forceFill_SetsUnfillableAttributes(){
		$definition = new DefinitionDouble;
		$definition->forceFill([ 'foo' => 'Fizz', 'bar' => 'Buzz' ]);

		$this->assertEquals($definition->bar, 'Buzz');
	}


	/**
	 * @test
	 */
	public function fromDefinition_WhenValidJson_ReturnsInstance(){
		$json = file_get_contents($this->definition);

		$definition = DefinitionDouble::fromDefinition($json);
		$this->assertInstanceOf(DefinitionDouble::class, $definition);
	}

	/**
	 * @test
	 */
	public function fromDefinition_WhenValidJson_PopulatesInstance(){
		$json = file_get_contents($this->definition);

		$definition = DefinitionDouble::fromDefinition($json);

		$this->assertEquals('Fizz', $definition->foo);
		$this->assertEquals('Buzz', $definition->bar);
		$this->assertNotEmpty($definition->fields);
	}

	/**
	 * @test
	 */
	public function fromDefinition_WhenInvalidJson_ThrowsInvalidJsonException(){
		$json = file_get_contents($this->definition);

        $this->expectException(JsonDecodeException::class);
		$definition = DefinitionDouble::fromDefinition('{ "foo": ');
	}



	/**
	 * @test
	 */
	public function fromDefinitionFile_WhenFileFound_ReturnsInstance(){
		$definition = DefinitionDouble::fromDefinitionFile($this->definition);
		$this->assertInstanceOf(DefinitionDouble::class, $definition);
	}

	/**
	 * @test
	 */
	public function fromDefinitionFile_WhenFileIsFound_PopulatesInstance(){
		$definition = DefinitionDouble::fromDefinitionFile($this->definition);

		$this->assertEquals('Fizz', $definition->foo);
		$this->assertEquals('Buzz', $definition->bar);
		$this->assertNotEmpty($definition->fields);
	}

	/**
	 * @test
	 */
	public function fromDefinitionFile_WhenFileIsNotFound_ThrowsException(){
        $this->expectException(FileNotFoundException::class);
		DefinitionDouble::fromDefinitionFile(sys_get_temp_dir() . '/foobar');
	}

}
