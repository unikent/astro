<?php
namespace Tests\Unit\Validation\Brokers;

use Config;
use Tests\TestCase;
use App\Validation\Brokers\BlockBroker;
use Illuminate\Validation\ValidationException;
use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definitions\Region as RegionDefinition;
use Illuminate\Validation\Validator as LaravelValidator;

class BlockBrokerTest extends TestCase
{

	protected $block;


	public function setUp()
	{
		parent::setUp();

		Config::set('app.definitions_path', base_path('tests/Support/Fixtures/definitions'));

        $file = BlockDefinition::locateDefinition('test-block');
        $this->block = BlockDefinition::fromDefinitionFile($file);
	}


	public function tearDown()
	{
		unset($this->block);
	}


	/**
	 * @test
	 */
	public function getRules_ReturnsValidationRulesFromDefinition()
	{
		$bv = new BlockBroker($this->block);
		$rules = $bv->getRules();

		$this->assertArrayHasKey('content', $rules);
		$this->assertNotEmpty($rules['content']);

		$this->assertArrayHasKey('title_of_widget', $rules);
		$this->assertNotEmpty($rules['title_of_widget']);

		$this->assertArrayHasKey('number_of_widgets', $rules);
		$this->assertNotEmpty($rules['number_of_widgets']);
	}

	/**
	 * @test
	 */
	public function getRules_WhenDefinitionHasRequiredRule_TransformsRequiredRule()
	{
		$bv = new BlockBroker($this->block);
		$rules = $bv->getRules();

		$this->assertArrayHasKey('content', $rules);
		$this->assertContains('present', $rules['content']);
		$this->assertContains('required', $rules['content']);
	}

	/**
	 * @test
	 */
	public function getRules_WhenDefinitionHasMinLengthRule_TransformsMinLengthRule()
	{
		$bv = new BlockBroker($this->block);
		$rules = $bv->getRules();

		$this->assertArrayHasKey('title_of_widget', $rules);
		$this->assertContains('string', $rules['title_of_widget']);
		$this->assertContains('min:1', $rules['title_of_widget']);
	}

	/**
	 * @test
	 */
	public function getRules_WhenDefinitionHasMaxLengthRule_TransformsMaxLengthRule()
	{
		$bv = new BlockBroker($this->block);
		$rules = $bv->getRules();

		$this->assertArrayHasKey('title_of_widget', $rules);
		$this->assertContains('string', $rules['title_of_widget']);
		$this->assertContains('max:50', $rules['title_of_widget']);
	}

	/**
	 * @test
	 */
	public function getRules_WhenDefinitionHasMinValueRule_TransformsMinValueRule()
	{
		$bv = new BlockBroker($this->block);
		$rules = $bv->getRules();

		$this->assertArrayHasKey('number_of_widgets', $rules);
		$this->assertContains('integer', $rules['number_of_widgets']);
		$this->assertContains('min:1', $rules['number_of_widgets']);
	}

	/**
	 * @test
	 */
	public function getRules_WhenDefinitionHasMaxValueRule_TransformsMaxValueRule()
	{
		$bv = new BlockBroker($this->block);
		$rules = $bv->getRules();

		$this->assertArrayHasKey('number_of_widgets', $rules);
		$this->assertContains('integer', $rules['number_of_widgets']);
		$this->assertContains('max:100', $rules['number_of_widgets']);
	}



	/**
	 * @test
	 */
	public function getValidator_ReturnsValidatorInstance()
	{
		$bv = new BlockBroker($this->block);
		$this->assertInstanceOf(LaravelValidator::class, $bv->getValidator());
	}

	/**
	 * @test
	 */
	public function getValidator_SetsRulesOnValidator()
	{
		$bv = new BlockBroker($this->block);

		$rules = $bv->getRules();
		$validator = $bv->getValidator();

		$this->assertEquals($rules, $validator->getRules());
	}

	/**
	 * @test
	 */
	public function getValidator_WhenDataIsProvided_SetsDataOnValidator()
	{
		$bv = new BlockBroker($this->block);

		$data = [ 'number_of_widgets' => 23 ];
		$validator = $bv->getValidator($data);

		$this->assertEquals($data, $validator->getData());
	}

	/**
	 * @test
	 */
	public function getValidator_WhenMessagesAreProvided_SetsMessagesOnValidator()
	{
		$bv = new BlockBroker($this->block);

		$messages = [ 'number_of_widgets' => 'Foobar!' ];
		$validator = $bv->getValidator([], $messages);

		$this->assertEquals($messages, $validator->customMessages);
	}



	/**
	 * @test
	 */
	public function validate_WhenInvalid_ThrowsException()
	{
		$bv = new BlockBroker($this->block);

		$data = [ 'number_of_widgets' => 101 ];

        $this->expectException(ValidationException::class);
		$bv->validate();
	}

}
