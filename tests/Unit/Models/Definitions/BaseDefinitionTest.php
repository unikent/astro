<?php
namespace Tests\Unit\Models\Definitions;

use Config;
use Exception;
use Tests\TestCase;
use App\Models\Definitions\BaseDefinition;
use Illuminate\Support\Facades\Redis;
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

	public function dynamicClassNameData()
	{
		return [
			[ 'block-v1', 'BlockV1'],
			[ '_-block-__123-v3', 'Block123V3'],
			[ 'some--other-definition---v4', 'SomeOtherDefinitionV4']
		];
	}

	/**
	 * @test
	 * @dataProvider dynamicClassNameData
	 * @param $input
	 * @param $expected
	 */
	public function getDynamicClassName_worksAsExpected($input, $expected)
	{
		$result = BaseDefinition::getDynamicClassName($input);
		$this->assertEquals($expected, $result);
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

	/**
	 * @test
	 */
	public function fromDefinitionFile_WhenNotCachedInRedis_LoadsFromFile(){
		if (Config::get('database.redis.active')) {
			Redis::flushDB();
			$definition_from_redis = Redis::get($this->definition);
			$this->assertNull($definition_from_redis);
			$definition = DefinitionDouble::fromDefinitionFile($this->definition);
			$definition_from_redis = Redis::get($this->definition);
			$this->assertNotNull($definition_from_redis);
		}
		else {
			$this->markTestSkipped('Redis not configured');
		}
	}

	/**
	 * @test
	 */
	public function fromDefinitionFile_WhenCachedInRedis_LoadsFromRedis(){
		if (Config::get('database.redis.active')) {
			Redis::flushDB();
			Redis::set($this->definition, file_get_contents($this->definition));
			$definition = DefinitionDouble::fromDefinitionFile($this->definition);
			$this->assertNotNull($definition);
		}
		else {
			$this->markTestSkipped('Redis not configured');
		}
	}

	/**
	 * @test
	 */
	public function fromDefinitionFile_WhenRedisNotAvailable_ThrowsException(){
		$this->expectException(Exception::class);
		if (Config::get('database.redis.active')) {
			Config::set('database.redis.default.port', -1);
			Redis::flushDB();
		}
		else {
			$this->markTestSkipped('Redis not configured');
		}
	}

	/**
	 * @test
	 @group wip
	 */
	public function fromDefinitionFile_WhenRedisNotAvailable_LoadsFromFile(){
		if (Config::get('database.redis.active')) {
			Config::set('database.redis.default.port', -1);
			$definition = DefinitionDouble::fromDefinitionFile($this->definition);
			$this->assertNotNull($definition);
		}
		else {
			$this->markTestSkipped('Redis not configured');
		}
	}
}
